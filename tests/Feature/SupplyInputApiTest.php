<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\SupplyContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplyInputApiTest extends TestCase
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

    public function test_can_create_supply_contract_and_demand_inputs(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
        Sanctum::actingAs($user);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $contractResponse = $this->postJson('/api/v1/supply-contracts', [
            'customer_name' => 'Sieu thi A',
            'customer_type' => 'retail',
            'crop_id' => $crop->id,
            'quantity' => 300,
            'unit' => 'kg',
            'frequency' => 'monthly',
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-31',
            'notes' => 'Monthly cucumber supply',
        ]);

        $contractResponse
            ->assertCreated()
            ->assertJsonPath('data.customer_name', 'Sieu thi A')
            ->assertJsonPath('data.quantity', '300.00')
            ->assertJsonPath('data.frequency', 'monthly');

        $contractId = $contractResponse->json('data.id');

        $demandResponse = $this->postJson('/api/v1/supply-demands', [
            'supply_contract_id' => $contractId,
            'crop_id' => $crop->id,
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'target_date' => '2026-06-15',
        ]);

        $demandResponse
            ->assertCreated()
            ->assertJsonPath('data.supply_contract_id', $contractId)
            ->assertJsonPath('data.quantity', '10.00')
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_supply_contract_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
        Sanctum::actingAs($user);

        $crop = Crop::create([
            'name' => 'Rau cai',
            'group' => 'leafy',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->postJson('/api/v1/supply-contracts', [
            'customer_name' => 'Sieu thi B',
            'crop_id' => $crop->id,
            'quantity' => 100,
            'unit' => 'kg',
            'frequency' => 'weekly',
            'start_date' => '2026-07-01',
            'end_date' => '2026-06-01',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }
}
