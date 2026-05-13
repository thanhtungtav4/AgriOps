<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\PriceTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CostingPriceMarginApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Crop $crop;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create(['name' => 'Farm Margin', 'code' => 'FM', 'status' => 'active']);
        $this->crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
        $this->manager = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_production_plan_uses_active_price_table_to_calculate_estimated_margin(): void
    {
        Sanctum::actingAs($this->manager);

        PriceTable::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'unit' => 'kg',
            'grade_a_price' => 30000,
            'grade_b_price' => 18000,
            'grade_c_price' => 10000,
            'side_channel_price' => 6000,
            'effective_from' => '2026-05-01',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/production-plans', [
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'quantity' => 100,
            'unit' => 'kg',
            'target_delivery_date' => '2026-05-20',
            'estimated_cost' => 1800000,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.estimated_cost', '1800000.00')
            ->assertJsonPath('data.estimated_revenue', '3000000.00')
            ->assertJsonPath('data.estimated_margin', '1200000.00')
            ->assertJsonPath('data.margin_percent', '40.00')
            ->assertJsonPath('data.pricing_snapshot.grade_a_price', 30000)
            ->assertJsonPath('data.pricing_snapshot.unit', 'kg');
    }

    public function test_cost_record_requires_canonical_cost_category(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/v1/cost-records', [
            'farm_id' => $this->farm->id,
            'amount' => 100000,
            'occurred_at' => '2026-05-13',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cost_category']);

        $this->postJson('/api/v1/cost-records', [
            'farm_id' => $this->farm->id,
            'cost_category' => 'random_cost',
            'amount' => 100000,
            'occurred_at' => '2026-05-13',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cost_category']);
    }

    public function test_margin_dashboard_summarizes_estimated_and_actual_values(): void
    {
        Sanctum::actingAs($this->manager);

        $planResponse = $this->postJson('/api/v1/production-plans', [
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'quantity' => 10,
            'unit' => 'kg',
            'target_delivery_date' => '2026-05-20',
            'estimated_cost' => 100000,
            'estimated_revenue' => 250000,
        ]);

        $planId = $planResponse->json('data.id');

        $this->postJson('/api/v1/cost-records', [
            'farm_id' => $this->farm->id,
            'production_plan_id' => $planId,
            'cost_category' => 'labor',
            'amount' => 45000,
            'occurred_at' => '2026-05-13',
        ])->assertCreated();

        $this->getJson('/api/v1/margin-dashboard')
            ->assertOk()
            ->assertJsonPath('data.estimated_revenue', 250000)
            ->assertJsonPath('data.estimated_cost', 100000)
            ->assertJsonPath('data.estimated_margin', 150000)
            ->assertJsonPath('data.actual_cost', 45000)
            ->assertJsonPath('data.cost_record_count', 1);
    }
}
