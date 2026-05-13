<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\HarvestModel;
use App\Models\LaborNorm;
use App\Models\LossProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanningApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_planning_calculation_returns_core_outputs_for_complete_norms(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        Sanctum::actingAs(User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]));

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
            'sale_price_per_unit' => 20000,
        ]);

        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'name' => 'DL01',
            'code' => 'DL01',
        ]);

        LossProfile::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'harvest_loss_percent' => 5,
            'processing_loss_percent' => 5,
            'packing_loss_percent' => 5,
            'non_grade_a_percent' => 3,
            'reject_percent' => 2,
        ]);

        HarvestModel::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'avg_yield_per_plant' => 0.5,
            'planting_density_per_m2' => 4,
            'survival_rate' => 90,
            'days_to_first_harvest' => 35,
        ]);

        LaborNorm::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'hours_per_m2' => 0.2,
            'cost_per_m2' => 12000,
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'farm_id' => $farm->id,
            'target_date' => '2026-06-30',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('output.delivery_quantity', 10)
            ->assertJsonPath('output.raw_harvest_quantity', 12.5)
            ->assertJsonPath('output.plants_needed_estimate', 25)
            ->assertJsonPath('output.plants_needed_execution', 25)  // ceil(25) = 25
            ->assertJsonPath('output.plants_to_plant_estimate', 27.78)
            ->assertJsonPath('output.plants_to_plant_execution', 28)  // ceil(27.78) = 28
            ->assertJsonPath('output.area_m2', 6.94)
            ->assertJsonPath('output.labor_hours', 1.39)
            ->assertJsonPath('output.estimated_workers', 0.17)
            ->assertJsonPath('output.estimated_cost', 83333.33)
            ->assertJsonPath('output.estimated_revenue', 200000)
            ->assertJsonPath('output.margin_percent', 58.33)
            ->assertJsonPath('output.days_to_first_harvest', 35)
            ->assertJsonPath('output.estimated_planting_date', '2026-05-26');
    }

    public function test_planning_calculation_returns_domain_error_when_norms_are_missing(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        Sanctum::actingAs(User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]));

        $crop = Crop::create([
            'name' => 'Rau cai',
            'group' => 'leafy',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'target_date' => '2026-06-30',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'PLANNING_ERROR')
            ->assertJsonPath('error.message', "Missing loss profile for crop 'Rau cai'. Configure harvest, processing, packing, grade, and reject loss percentages.");
    }

    public function test_happy_path_includes_assumptions_and_fulfillment(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        Sanctum::actingAs(User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]));

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
            'sale_price_per_unit' => 20000,
        ]);

        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'name' => 'DL01',
            'code' => 'DL01',
        ]);

        LossProfile::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'harvest_loss_percent' => 5,
            'processing_loss_percent' => 5,
            'packing_loss_percent' => 5,
            'non_grade_a_percent' => 3,
            'reject_percent' => 2,
        ]);

        HarvestModel::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'avg_yield_per_plant' => 0.5,
            'planting_density_per_m2' => 4,
            'survival_rate' => 90,
            'days_to_first_harvest' => 35,
        ]);

        LaborNorm::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'hours_per_m2' => 0.2,
            'cost_per_m2' => 12000,
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'farm_id' => $farm->id,
            'target_date' => '2026-06-30',
        ]);

        $response->assertOk();

        // Check assumptions object exists and has required fields
        $response->assertJsonStructure([
            'assumptions' => [
                'formula_version',
                'loss_model',
                'season',
                'climate_zone',
                'season_factor',
                'climate_factor',
                'price_source',
                'rounding_profile',
            ],
        ]);

        // Check fulfillment object exists and has required fields
        $response->assertJsonStructure([
            'fulfillment' => [
                'status',
                'shortages',
                'warnings',
            ],
        ]);

        // Status should be 'warning' when season/climate not specified
        $response->assertJsonPath('fulfillment.status', 'warning');
        $response->assertJsonPath('fulfillment.warnings', ['season_not_specified', 'climate_zone_not_specified']);
    }

    public function test_plant_execution_quantities_round_up(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        Sanctum::actingAs(User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]));

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
            'sale_price_per_unit' => 20000,
        ]);

        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'name' => 'DL01',
            'code' => 'DL01',
        ]);

        LossProfile::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'harvest_loss_percent' => 5,
            'processing_loss_percent' => 5,
            'packing_loss_percent' => 5,
            'non_grade_a_percent' => 3,
            'reject_percent' => 2,
        ]);

        HarvestModel::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'avg_yield_per_plant' => 0.5,
            'planting_density_per_m2' => 4,
            'survival_rate' => 90,
            'days_to_first_harvest' => 35,
        ]);

        LaborNorm::create([
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'hours_per_m2' => 0.2,
            'cost_per_m2' => 12000,
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'kg',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'farm_id' => $farm->id,
            'target_date' => '2026-06-30',
        ]);

        $response->assertOk();

        // Plant counts should have both estimate (decimal) and execution (whole number) values
        $response->assertJsonStructure([
            'output' => [
                'plants_needed_estimate',
                'plants_needed_execution',
                'plants_to_plant_estimate',
                'plants_to_plant_execution',
            ],
        ]);

        // Execution values should be whole numbers (rounded up)
        $data = $response->json();
        $this->assertEquals(floor($data['output']['plants_needed_execution']), $data['output']['plants_needed_execution']);
        $this->assertEquals(floor($data['output']['plants_to_plant_execution']), $data['output']['plants_to_plant_execution']);

        // Execution should be >= estimate (rounded up)
        $this->assertGreaterThanOrEqual($data['output']['plants_needed_estimate'], $data['output']['plants_needed_execution']);
        $this->assertGreaterThanOrEqual($data['output']['plants_to_plant_estimate'], $data['output']['plants_to_plant_execution']);
    }

    public function test_unsupported_unit_returns_field_level_planning_error(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        Sanctum::actingAs(User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]));

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
            'sale_price_per_unit' => 20000,
        ]);

        $response = $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'unsupported_unit',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'target_date' => '2026-06-30',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('error.code', 'PLANNING_ERROR');
        $response->assertJsonStructure([
            'error' => [
                'code',
                'message',
                'details' => [
                    'field',
                ],
                'trace_id',
            ],
        ]);
    }
}
