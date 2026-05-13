<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantingBatchFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_planting_batch_can_be_created(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $this->assertNotNull($batch->id);
        $this->assertEquals($farm->id, $batch->farm_id);
        $this->assertEquals($crop->id, $batch->crop_id);
    }

    public function test_planting_batch_can_have_multiple_allocations(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $plot1 = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $plot2 = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-B',
            'name' => 'Plot B',
            'area_m2' => 150,
            'status' => 'available',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 200,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $allocation1 = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot1->id,
            'allocated_area_m2' => 100,
            'status' => 'allocated',
        ]);

        $allocation2 = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot2->id,
            'allocated_area_m2' => 150,
            'status' => 'allocated',
        ]);

        $batch->refresh();
        $this->assertCount(2, $batch->allocations);
        $this->assertTrue($batch->allocations->contains($allocation1));
        $this->assertTrue($batch->allocations->contains($allocation2));
    }

    public function test_allocation_cannot_orphan_from_batch(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 100,
            'status' => 'allocated',
        ]);

        $allocationId = $batch->allocations->first()->id;
        $batch->delete();

        $this->assertNull(PlantingBatchAllocation::find($allocationId));
    }

    public function test_allocation_cannot_orphan_from_plot(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 100,
            'status' => 'allocated',
        ]);

        $allocationId = $allocation->id;
        $plot->delete();

        $this->assertNull(PlantingBatchAllocation::find($allocationId));
    }

    public function test_planned_fields_remain_separate_from_actual_fields(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'actual_quantity' => null,
            'actual_area_m2' => null,
            'actual_start_date' => null,
            'actual_harvest_date' => null,
            'status' => 'planned',
        ]);

        $this->assertEquals(100, $batch->planned_quantity);
        $this->assertEquals('kg', $batch->planned_unit);
        $this->assertEquals(50, $batch->planned_area_m2);
        $this->assertEquals('2026-06-01', $batch->planned_start_date->format('Y-m-d'));
        $this->assertEquals('2026-07-06', $batch->planned_harvest_date->format('Y-m-d'));

        $this->assertNull($batch->actual_quantity);
        $this->assertNull($batch->actual_area_m2);
        $this->assertNull($batch->actual_start_date);
        $this->assertNull($batch->actual_harvest_date);

        $batch->update([
            'actual_quantity' => 95,
            'actual_area_m2' => 48,
            'actual_start_date' => '2026-06-03',
            'actual_harvest_date' => '2026-07-08',
            'status' => 'harvesting',
        ]);

        $batch->refresh();

        $this->assertEquals(100, $batch->planned_quantity);
        $this->assertEquals('2026-06-01', $batch->planned_start_date->format('Y-m-d'));

        $this->assertEquals(95, $batch->actual_quantity);
        $this->assertEquals(48, $batch->actual_area_m2);
        $this->assertEquals('2026-06-03', $batch->actual_start_date->format('Y-m-d'));
        $this->assertEquals('2026-07-08', $batch->actual_harvest_date->format('Y-m-d'));
    }

    public function test_batch_has_farm_and_crop_references(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'name' => 'DL Hybrid',
            'code' => 'DL01',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'variety_id' => $variety->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $this->assertNotNull($batch->farm);
        $this->assertEquals($farm->id, $batch->farm->id);

        $this->assertNotNull($batch->crop);
        $this->assertEquals($crop->id, $batch->crop->id);

        $this->assertNotNull($batch->variety);
        $this->assertEquals($variety->id, $batch->variety->id);
    }

    public function test_batch_has_optional_production_plan_reference(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $this->assertNull($batch->production_plan_id);
        $this->assertNull($batch->productionPlan);
    }

    public function test_batch_status_uses_conservative_enum(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $this->assertEquals('planned', $batch->status);
        $this->assertContains($batch->status, ['planned', 'planned_kh', 'approved', 'soil_prep', 'planting', 'growing', 'harvesting', 'completed', 'cancelled']);

        $batch->update(['status' => 'planned_kh']);
        $this->assertEquals('planned_kh', $batch->status);

        $batch->update(['status' => 'approved']);
        $this->assertEquals('approved', $batch->status);
    }

    public function test_allocation_links_to_correct_plot(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 100,
            'status' => 'allocated',
        ]);

        $this->assertNotNull($allocation->plot);
        $this->assertEquals($plot->id, $allocation->plot->id);
        $this->assertEquals('Plot A', $allocation->plot->name);
    }

    public function test_allocation_has_status_tracking(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 100,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 100,
            'status' => 'allocated',
        ]);

        $this->assertEquals('allocated', $allocation->status);

        $allocation->update(['status' => 'active']);
        $this->assertEquals('active', $allocation->status);

        $allocation->update(['status' => 'completed']);
        $this->assertEquals('completed', $allocation->status);
    }

    public function test_batch_model_has_allocations_relationship(): void
    {
        $batch = new PlantingBatch();
        $this->assertTrue(method_exists($batch, 'allocations'));
    }

    public function test_batch_model_has_plot_relationship(): void
    {
        $batch = new PlantingBatch();
        $this->assertTrue(method_exists($batch, 'plots'));
    }

    public function test_batch_model_has_production_plan_relationship(): void
    {
        $batch = new PlantingBatch();
        $this->assertTrue(method_exists($batch, 'productionPlan'));
    }

    public function test_allocation_model_has_batch_relationship(): void
    {
        $allocation = new PlantingBatchAllocation();
        $this->assertTrue(method_exists($allocation, 'batch'));
    }

    public function test_allocation_model_has_plot_relationship(): void
    {
        $allocation = new PlantingBatchAllocation();
        $this->assertTrue(method_exists($allocation, 'plot'));
    }
}
