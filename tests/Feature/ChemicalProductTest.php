<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ChemicalProduct;
use App\Models\Farm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChemicalProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $farmManager;
    private User $worker;
    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::factory()->create();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->farmManager = User::factory()->create([
            'role' => 'farm_manager',
            'farm_id' => $this->farm->id,
        ]);
        $this->worker = User::factory()->create([
            'role' => 'worker',
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_farm_manager_can_create_product(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $response = $this->postJson('/api/v1/chemical-products', [
            'name' => 'Roundup 480 SL',
            'active_ingredient' => 'Glyphosate',
            'type' => 'herbicide',
            'unit' => 'lít',
            'stock_quantity' => 100,
            'min_stock_level' => 20,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Roundup 480 SL')
            ->assertJsonPath('data.type', 'herbicide');
    }

    public function test_worker_cannot_create_product(): void
    {
        $this->actingAs($this->worker, 'sanctum');

        $response = $this->postJson('/api/v1/chemical-products', [
            'name' => 'Test Product',
            'active_ingredient' => 'Test',
            'type' => 'pesticide',
            'unit' => 'kg',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_list_products(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        ChemicalProduct::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/chemical-products');

        $response->assertOk()
            ->assertJsonCount(5, 'data.data');
    }

    public function test_can_filter_by_type(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        ChemicalProduct::factory()->create(['type' => 'pesticide']);
        ChemicalProduct::factory()->create(['type' => 'fertilizer']);

        $response = $this->getJson('/api/v1/chemical-products?type=pesticide&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_can_update_stock(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $product = ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'stock_quantity' => 100,
        ]);

        $response = $this->postJson("/api/v1/chemical-products/{$product->id}/stock", [
            'adjustment' => 50,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.stock_quantity', '150.000');
    }

    public function test_cannot_reduce_stock_below_zero(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $product = ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson("/api/v1/chemical-products/{$product->id}/stock", [
            'adjustment' => -20,
        ]);

        $response->assertStatus(422);
    }

    public function test_low_stock_endpoint(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        ChemicalProduct::factory()->create(['stock_quantity' => 5, 'min_stock_level' => 10]);
        ChemicalProduct::factory()->create(['stock_quantity' => 100, 'min_stock_level' => 20]);

        $response = $this->getJson('/api/v1/chemical-products/low-stock');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
    
    public function test_farm_manager_cannot_access_another_farm_product(): void
    {
        $anotherFarm = Farm::factory()->create();
        $product = ChemicalProduct::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        // Try to view another farm's product
        $response = $this->getJson("/api/v1/chemical-products/{$product->id}");
        $response->assertForbidden();
        
        // Try to update another farm's product
        $response = $this->patchJson("/api/v1/chemical-products/{$product->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_update_stock_for_another_farm_product(): void
    {
        $anotherFarm = Farm::factory()->create();
        $product = ChemicalProduct::factory()->create([
            'farm_id' => $anotherFarm->id,
            'stock_quantity' => 100,
        ]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/chemical-products/{$product->id}/stock", [
            'adjustment' => 50,
        ]);
        
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_create_product_for_another_farm(): void
    {
        $anotherFarm = Farm::factory()->create();
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson('/api/v1/chemical-products', [
            'farm_id' => $anotherFarm->id,
            'name' => 'Test Product for Another Farm',
            'active_ingredient' => 'Test Ingredient',
            'type' => 'pesticide',
            'unit' => 'kg',
        ]);
        
        $response->assertForbidden();
    }

    public function test_farm_manager_can_view_but_not_mutate_global_product(): void
    {
        $product = ChemicalProduct::factory()->create([
            'farm_id' => null,
            'stock_quantity' => 100,
        ]);

        $this->actingAs($this->farmManager, 'sanctum');

        $this->getJson("/api/v1/chemical-products/{$product->id}")
            ->assertOk();

        $this->patchJson("/api/v1/chemical-products/{$product->id}", [
            'name' => 'Changed global product',
        ])->assertForbidden();

        $this->postJson("/api/v1/chemical-products/{$product->id}/stock", [
            'adjustment' => 5,
        ])->assertForbidden();
    }
}
