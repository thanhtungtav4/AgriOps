<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF',
            'total_area_m2' => 1000,
        ]);
    }

    public function test_repeated_invalid_logins_are_throttled(): void
    {
        // Create a user with valid credentials
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
            'farm_id' => $this->farm->id,
        ]);

        // Attempt 5 logins (matching throttle:5,1 limit) - all with wrong password
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
            $response->assertUnauthorized();
        }

        // The 6th attempt should be throttled (429 Too Many Requests)
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_valid_login_succeeds_before_rate_limit(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
            'farm_id' => $this->farm->id,
        ]);

        // Valid login should work
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_at',
            ],
        ]);
    }

    public function test_rate_limit_response_format(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'farm_id' => $this->farm->id,
        ]);

        // Exhaust rate limit
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'message' => 'Too Many Attempts.',
        ]);
    }
}
