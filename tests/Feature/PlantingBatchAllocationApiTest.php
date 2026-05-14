<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantingBatchAllocationApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Crop $crop;
    private User $manager;
    private PlantingBatch $batch;
    private Plot $plot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $this->crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->manager = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);

        $this->batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'status' => 'approved',
        ]);

        $this->plot = Plot::create([
            'farm_id' => $this->farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 300,
            'status' => 'available',
        ]);
    }

    public function test_manager_can_allocate_plot_to_batch(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson("/api/v1/planting-batches/{$this->batch->id}/allocations", [
            'plot_id' => $this->plot->id,
            'allocated_area_m2' => 120,
            'notes' => 'Allocate north section',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.plot_id', $this->plot->id);

        $this->assertDatabaseHas('planting_batch_allocations', [
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'allocated_area_m2' => 120,
        ]);

        $this->assertDatabaseHas('plots', [
            'id' => $this->plot->id,
            'current_batch_id' => $this->batch->id,
        ]);
    }

    public function test_allocation_rejects_cross_farm_plot(): void
    {
        Sanctum::actingAs($this->manager);

        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 2000,
        ]);

        $otherPlot = Plot::create([
            'farm_id' => $otherFarm->id,
            'code' => 'PLOT-B',
            'name' => 'Plot B',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $this->postJson("/api/v1/planting-batches/{$this->batch->id}/allocations", [
            'plot_id' => $otherPlot->id,
            'allocated_area_m2' => 50,
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'farm_mismatch');
    }

    public function test_manager_can_remove_allocation(): void
    {
        Sanctum::actingAs($this->manager);

        $allocationId = $this->postJson("/api/v1/planting-batches/{$this->batch->id}/allocations", [
            'plot_id' => $this->plot->id,
            'allocated_area_m2' => 120,
        ])->json('data.id');

        $this->deleteJson("/api/v1/planting-batches/{$this->batch->id}/allocations/{$allocationId}", [
            'reason' => 'Move crop to another plot',
        ])->assertOk();

        $this->assertDatabaseMissing('planting_batch_allocations', [
            'id' => $allocationId,
        ]);
    }
}

