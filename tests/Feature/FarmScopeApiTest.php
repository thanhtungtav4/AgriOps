<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\Plot;
use App\Models\ProductionPlan;
use App\Models\SupplyContract;
use App\Models\SupplyDemand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FarmScopeApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farmA;
    private Farm $farmB;
    private User $userA;
    private User $userB;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farmA = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        $this->farmB = Farm::create([
            'name' => 'Farm B',
            'code' => 'FB',
            'total_area_m2' => 2000,
        ]);

        $this->userA = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farmA->id,
        ]);

        $this->userB = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farmB->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'farm_id' => null,
        ]);

        Plot::create([
            'farm_id' => $this->farmA->id,
            'code' => 'PA1',
            'name' => 'Plot A1',
            'area_m2' => 500,
            'status' => 'available',
        ]);

        Plot::create([
            'farm_id' => $this->farmB->id,
            'code' => 'PB1',
            'name' => 'Plot B1',
            'area_m2' => 1000,
            'status' => 'available',
        ]);
    }

    public function test_farm_manager_sees_only_own_farm_on_farm_list(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/v1/farms');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.code', 'FA');
    }

    public function test_admin_sees_all_farms_on_farm_list(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/farms');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_farm_manager_cannot_view_other_farm(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/v1/farms/' . $this->farmB->id);

        $response->assertForbidden();
        $response->assertJsonPath('error.code', 'AUTH_FORBIDDEN');
    }

    public function test_admin_can_view_any_farm(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/farms/' . $this->farmB->id);

        $response->assertOk();
        $response->assertJsonPath('data.code', 'FB');
    }

    public function test_farm_manager_sees_only_own_plots(): void
    {
        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/v1/plots');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.code', 'PA1');
    }

    public function test_farm_manager_cannot_view_other_farm_plot(): void
    {
        Sanctum::actingAs($this->userA);

        $plotB = Plot::where('farm_id', $this->farmB->id)->first();

        $response = $this->getJson('/api/v1/plots/' . $plotB->id);

        $response->assertForbidden();
        $response->assertJsonPath('error.code', 'AUTH_FORBIDDEN');
    }

    public function test_supply_contracts_scoped_to_farm(): void
    {
        Sanctum::actingAs($this->userA);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        SupplyContract::create([
            'farm_id' => $this->farmA->id,
            'customer_name' => 'Customer A',
            'crop_id' => $crop->id,
            'quantity' => 100,
            'unit' => 'kg',
            'start_date' => now(),
        ]);

        SupplyContract::create([
            'farm_id' => $this->farmB->id,
            'customer_name' => 'Customer B',
            'crop_id' => $crop->id,
            'quantity' => 200,
            'unit' => 'kg',
            'start_date' => now(),
        ]);

        $response = $this->getJson('/api/v1/supply-contracts');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.customer_name', 'Customer A');
    }

    public function test_supply_demands_scoped_to_farm(): void
    {
        Sanctum::actingAs($this->userA);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        SupplyDemand::create([
            'farm_id' => $this->farmA->id,
            'crop_id' => $crop->id,
            'quantity' => 50,
            'unit' => 'kg',
            'target_date' => now()->addDays(7),
        ]);

        SupplyDemand::create([
            'farm_id' => $this->farmB->id,
            'crop_id' => $crop->id,
            'quantity' => 75,
            'unit' => 'kg',
            'target_date' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/v1/supply-demands');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.quantity', '50.00');
    }

    public function test_production_plans_scoped_to_farm(): void
    {
        Sanctum::actingAs($this->userA);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        ProductionPlan::create([
            'farm_id' => $this->farmA->id,
            'crop_id' => $crop->id,
            'quantity' => 10,
            'unit' => 'kg',
            'target_delivery_date' => now()->addDays(14),
        ]);

        ProductionPlan::create([
            'farm_id' => $this->farmB->id,
            'crop_id' => $crop->id,
            'quantity' => 20,
            'unit' => 'kg',
            'target_delivery_date' => now()->addDays(14),
        ]);

        $response = $this->getJson('/api/v1/production-plans');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.quantity', '10.00');
    }

    public function test_farm_manager_cannot_calculate_plan_for_other_farm(): void
    {
        Sanctum::actingAs($this->userA);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'farm_id' => $this->farmB->id,
            'target_date' => '2026-06-30',
        ]);

        $response->assertForbidden();
        $response->assertJsonPath('error.code', 'AUTH_FORBIDDEN');
    }

    public function test_worker_role_also_scoped_to_farm(): void
    {
        $workerA = User::factory()->create([
            'role' => User::ROLE_WORKER,
            'farm_id' => $this->farmA->id,
        ]);

        Sanctum::actingAs($workerA);

        $response = $this->getJson('/api/v1/plots');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.code', 'PA1');
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $response = $this->getJson('/api/v1/plots');

        $response->assertUnauthorized();
    }

    public function test_user_without_farm_gets_403(): void
    {
        $userWithoutFarm = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => null,
        ]);

        Sanctum::actingAs($userWithoutFarm);

        $response = $this->getJson('/api/v1/plots');

        $response->assertForbidden();
        $response->assertJsonPath('error.code', 'AUTH_FORBIDDEN');
    }
}
