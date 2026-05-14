<?php

namespace Tests\Feature;

use App\Models\CostBreakdown;
use App\Models\CostRecord;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\ProductionPlan;
use App\Models\User;
use App\Services\CostBreakdownService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostBreakdownTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $farmManager;
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
    }

    public function test_admin_can_list_cost_breakdowns(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        CostBreakdown::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/cost-breakdowns');

        $response->assertOk()
            ->assertJsonCount(3, 'data.data');
    }

    public function test_farm_manager_can_calculate_breakdown_for_plan(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);

        // Create some cost records
        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'production_plan_id' => $plan->id,
            'cost_category' => 'seed',
            'amount' => 1000,
        ]);

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'production_plan_id' => $plan->id,
            'cost_category' => 'fertilizer',
            'amount' => 2000,
        ]);

        $response = $this->postJson("/api/v1/cost-breakdowns/plan/{$plan->id}");

        $response->assertStatus(201)
            ->assertJsonPath('data.breakdown_type', 'production_plan')
            ->assertJsonPath('data.production_plan_id', $plan->id)
            ->assertJsonPath('data.total_seed_cost', '1000.00')
            ->assertJsonPath('data.total_fertilizer_cost', '2000.00');
    }

    public function test_farm_manager_can_calculate_breakdown_for_batch(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $batch = PlantingBatch::factory()->create(['farm_id' => $this->farm->id]);

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'cost_category' => 'labor',
            'amount' => 5000,
        ]);

        $response = $this->postJson("/api/v1/cost-breakdowns/batch/{$batch->id}");

        $response->assertStatus(201)
            ->assertJsonPath('data.breakdown_type', 'planting_batch')
            ->assertJsonPath('data.planting_batch_id', $batch->id)
            ->assertJsonPath('data.total_labor_cost', '5000.00');
    }

    public function test_can_calculate_seasonal_breakdown(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'cost_category' => 'chemical_biological',
            'amount' => 3000,
            'occurred_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/cost-breakdowns/farm/{$this->farm->id}", [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.breakdown_type', 'farm')
            ->assertJsonPath('data.farm_id', $this->farm->id)
            ->assertJsonPath('data.total_chemical_cost', '3000.00');
    }

    public function test_dashboard_summary_returns_current_and_last_month(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        // Current month cost
        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'cost_category' => 'water',
            'amount' => 500,
            'occurred_at' => now(),
        ]);

        // Last month cost
        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'cost_category' => 'water',
            'amount' => 300,
            'occurred_at' => now()->subMonth(),
        ]);

        $response = $this->getJson('/api/v1/cost-breakdowns/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.current_month.total_cost', 500)
            ->assertJsonPath('data.last_month.total_cost', 300);
    }

    public function test_cost_breakdown_calculates_total_cost(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'production_plan_id' => $plan->id,
            'cost_category' => 'seed',
            'amount' => 1000,
        ]);

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'production_plan_id' => $plan->id,
            'cost_category' => 'fertilizer',
            'amount' => 2000,
        ]);

        CostRecord::factory()->create([
            'farm_id' => $this->farm->id,
            'production_plan_id' => $plan->id,
            'cost_category' => 'labor',
            'amount' => 3000,
        ]);

        $response = $this->postJson("/api/v1/cost-breakdowns/plan/{$plan->id}");

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertEquals(6000.00, (float) $data['total_cost']);
    }

    public function test_worker_cannot_calculate_breakdown(): void
    {
        $worker = User::factory()->create([
            'role' => 'worker',
            'farm_id' => $this->farm->id,
        ]);

        $this->actingAs($worker, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->postJson("/api/v1/cost-breakdowns/plan/{$plan->id}");

        $response->assertForbidden();
    }

    public function test_can_filter_breakdowns_by_type(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        CostBreakdown::factory()->create(['breakdown_type' => 'farm', 'farm_id' => $this->farm->id]);
        CostBreakdown::factory()->create(['breakdown_type' => 'production_plan', 'farm_id' => $this->farm->id]);

        $response = $this->getJson('/api/v1/cost-breakdowns?breakdown_type=farm&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data');
    }
    
    public function test_farm_manager_cannot_calculate_breakdown_for_another_farm_plan(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlan = ProductionPlan::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/cost-breakdowns/plan/{$anotherPlan->id}");
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_calculate_breakdown_for_another_farm_batch(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherBatch = PlantingBatch::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/cost-breakdowns/batch/{$anotherBatch->id}");
        $response->assertForbidden();
    }
}