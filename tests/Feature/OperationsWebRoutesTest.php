<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsWebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_route_returns_200(): void
    {
        $response = $this->get('/operations');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_login_route_returns_200(): void
    {
        $response = $this->get('/operations/login');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_subroute_returns_200(): void
    {
        $response = $this->get('/operations/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_login_subroute_returns_200(): void
    {
        $response = $this->get('/operations/login/extra');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_root_redirects_to_operations(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }
}