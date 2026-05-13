<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTokenPolicyTest extends TestCase
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

    public function test_login_returns_token_with_metadata(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'token',
                'token_type',
                'expires_at',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'farm_id',
                    'farm',
                ],
            ],
            'meta',
        ]);
        $response->assertJsonPath('data.token_type', 'Bearer');
        $response->assertJsonPath('data.user.email', 'test@example.com');
        $response->assertJsonPath('data.user.farm_id', $this->farm->id);
    }

    public function test_login_with_invalid_credentials_returns_standard_error(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
        $response->assertJsonPath('error.message', 'Invalid email or password.');
    }

    public function test_login_with_nonexistent_user_returns_standard_error(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $response->assertJsonPath('data.message', 'Logged out successfully.');
    }

    public function test_me_returns_user_with_scope_info(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'is_admin',
                'can_approve',
                'farm_id',
                'farm',
                'scope',
            ],
            'meta',
        ]);
        $response->assertJsonPath('data.scope', 'farm');
        $response->assertJsonPath('data.is_admin', false);
        $response->assertJsonPath('data.can_approve', true);
    }

    public function test_admin_me_returns_global_scope(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'farm_id' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.scope', 'global');
        $response->assertJsonPath('data.is_admin', true);
        $response->assertJsonPath('data.farm_id', null);
    }

    public function test_worker_cannot_approve(): void
    {
        $worker = User::factory()->create([
            'role' => User::ROLE_WORKER,
            'farm_id' => $this->farm->id,
        ]);

        Sanctum::actingAs($worker);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.can_approve', false);
    }

    public function test_protected_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/plots');

        $response->assertUnauthorized();
    }
}
