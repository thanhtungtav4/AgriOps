<?php

namespace Tests\Feature;

use App\Models\ChemicalUsage;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HarvestLotApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Farm $otherFarm;
    private User $manager;
    private Crop $crop;
    private PlantingBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create(['name' => 'Farm A', 'code' => 'FA', 'status' => 'active']);
        $this->otherFarm = Farm::create(['name' => 'Farm B', 'code' => 'FB', 'status' => 'active']);
        $this->manager = User::create([
            'name' => 'Manager',
            'email' => 'harvest-manager@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
        $this->crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
        $this->batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'fruiting',
            'planned_start_date' => '2026-05-01',
        ]);
    }

    public function test_can_create_harvest_lot_with_grade_breakdown_after_approved_inspection(): void
    {
        Sanctum::actingAs($this->manager);
        $this->approveBatchForHarvest();

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 100,
            'unit' => 'kg',
            'grade_a_quantity' => 80,
            'grade_b_quantity' => 10,
            'grade_c_quantity' => 5,
            'reject_quantity' => 5,
            'reject_reasons' => ['pest_damage' => 3, 'deformed' => 2],
            'notes' => 'First harvest.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.raw_quantity', '100.000')
            ->assertJsonPath('data.grade_a_quantity', '80.000')
            ->assertJsonPath('data.reject_reasons.pest_damage', 3);

        $this->batch->refresh();
        $this->assertEquals('harvesting', $this->batch->status);
        $this->assertEquals('100.00', $this->batch->actual_quantity);
        $this->assertEquals('2026-05-15', $this->batch->actual_harvest_date->toDateString());
    }

    public function test_grade_breakdown_cannot_exceed_raw_quantity(): void
    {
        Sanctum::actingAs($this->manager);
        $this->approveBatchForHarvest();

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 100,
            'grade_a_quantity' => 90,
            'grade_b_quantity' => 20,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'HARVEST_VALIDATION_FAILED');

        $this->assertEquals(0, HarvestLot::count());
    }

    public function test_raw_quantity_must_be_greater_than_zero(): void
    {
        Sanctum::actingAs($this->manager);
        $this->approveBatchForHarvest();

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 0,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, HarvestLot::count());
    }

    public function test_harvest_requires_approved_pre_harvest_inspection(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'HARVEST_INSPECTION_BLOCKED');
    }

    public function test_rejected_latest_inspection_blocks_harvest(): void
    {
        Sanctum::actingAs($this->manager);
        $this->approveBatchForHarvest('2026-05-13 08:00:00');
        $this->rejectBatchForHarvest('2026-05-14 08:00:00');

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'HARVEST_INSPECTION_BLOCKED');
    }

    public function test_active_isolation_blocks_harvest(): void
    {
        Sanctum::actingAs($this->manager);
        $this->approveBatchForHarvest();

        ChemicalUsage::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'product_name' => 'Fungicide B',
            'product_type' => 'chemical',
            'isolation_days' => 7,
            'applied_at' => '2026-05-13 09:00:00',
            'isolation_ends_at' => '2026-05-20 09:00:00',
        ]);

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $this->batch->id,
            'harvest_date' => '2026-05-16',
            'raw_quantity' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'HARVEST_ISOLATION_BLOCKED');
    }

    public function test_user_cannot_harvest_other_farm_batch(): void
    {
        Sanctum::actingAs($this->manager);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $this->otherFarm->id,
            'crop_id' => $this->crop->id,
            'status' => 'fruiting',
        ]);

        $response = $this->postJson('/api/v1/harvest-lots', [
            'planting_batch_id' => $otherBatch->id,
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 100,
        ]);

        $response->assertStatus(403);
    }

    private function approveBatchForHarvest(string $inspectedAt = '2026-05-13 08:00:00'): void
    {
        PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->manager->id,
            'approved_by_user_id' => $this->manager->id,
            'status' => 'approved',
            'inspected_at' => $inspectedAt,
            'approved_at' => $inspectedAt,
            'checklist' => [
                ['key' => 'maturity', 'label' => 'Maturity', 'passed' => true],
                ['key' => 'pest_free', 'label' => 'No active pest pressure', 'passed' => true],
            ],
        ]);
    }

    private function rejectBatchForHarvest(string $inspectedAt): void
    {
        PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->manager->id,
            'status' => 'rejected',
            'inspected_at' => $inspectedAt,
            'rejected_at' => $inspectedAt,
            'checklist' => [
                ['key' => 'maturity', 'label' => 'Maturity', 'passed' => false],
            ],
        ]);
    }
}
