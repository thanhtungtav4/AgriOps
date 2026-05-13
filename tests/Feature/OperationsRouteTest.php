<?php

namespace Tests\Feature;

use Tests\TestCase;

class OperationsRouteTest extends TestCase
{
    public function test_operations_route_returns_view(): void
    {
        $response = $this->get('/operations');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_route_with_subpath_returns_view(): void
    {
        $response = $this->get('/operations/login');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_route_with_any_subpath_returns_view(): void
    {
        $response = $this->get('/operations/dashboard/some/nested/path');

        $response->assertStatus(200);
        $response->assertViewIs('operations.app');
    }

    public function test_operations_view_contains_app_container(): void
    {
        $response = $this->get('/operations');

        $response->assertStatus(200);
        $response->assertSee('id="app"', false);
    }
}