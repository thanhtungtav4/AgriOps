<?php

namespace Tests\Feature;

use App\Exceptions\AllocationException;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use App\Services\PlantingBatchAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantingBatchAllocationGuardTest extends TestCase
{
    use RefreshDatabase;

    private PlantingBatchAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlantingBatchAllocationService();
    }

    private function createFarm(string $code = 'TF01'): Farm
    {
        return Farm::create([
            'name' => 'Test Farm',
            'code' => $code,
            'total_area_m2' => 5000,
        ]);
    }

    private function createCrop(): Crop
    {
        return Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
    }

    private function createBatch(Farm $farm, Crop $crop): PlantingBatch
    {
        return PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);
    }

    private function createPlot(Farm $farm, string $status = 'available', float $areaM2 = 100): Plot
    {
        return Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-' . uniqid(),
            'name' => 'Test Plot',
            'area_m2' => $areaM2,
            'status' => $status,
        ]);
    }

    public function test_allocation_rejects_plot_from_different_farm(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm1 = $this->createFarm('FA01');
        $farm2 = $this->createFarm('FA02');
        $crop = $this->createCrop();

        $batch = $this->createBatch($farm1, $crop);
        $plot = $this->createPlot($farm2);

        $this->expectException(AllocationException::class);
        $this->expectExceptionMessage('Cannot allocate: plot belongs to farm');

        $this->service->allocate($batch, $plot);
    }

    public function test_allocation_rejects_farm_mismatch_error_code(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm1 = $this->createFarm('FA01');
        $farm2 = $this->createFarm('FA02');
        $crop = $this->createCrop();

        $batch = $this->createBatch($farm1, $crop);
        $plot = $this->createPlot($farm2);

        try {
            $this->service->allocate($batch, $plot);
            $this->fail('Expected AllocationException not thrown');
        } catch (AllocationException $e) {
            $this->assertEquals(AllocationException::CODE_FARM_MISMATCH, $e->errorCode);
            $this->assertArrayHasKey('batch_farm_id', $e->context);
            $this->assertArrayHasKey('plot_farm_id', $e->context);
        }
    }

    public function test_allocation_rejects_inactive_plot(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'suspended');

        $this->expectException(AllocationException::class);
        $this->expectExceptionMessage("plot status 'suspended' is not available");

        $this->service->allocate($batch, $plot);
    }

    public function test_allocation_rejects_rest_plot(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'rest');

        $this->expectException(AllocationException::class);

        $this->service->allocate($batch, $plot);
    }

    public function test_allocation_rejects_restoring_plot(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'restoring');

        $this->expectException(AllocationException::class);

        $this->service->allocate($batch, $plot);
    }

    public function test_allocation_rejects_plot_unavailable_error_code(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'suspended');

        try {
            $this->service->allocate($batch, $plot);
            $this->fail('Expected AllocationException not thrown');
        } catch (AllocationException $e) {
            $this->assertEquals(AllocationException::CODE_PLOT_UNAVAILABLE, $e->errorCode);
            $this->assertArrayHasKey('plot_status', $e->context);
        }
    }

    public function test_allocation_allows_plot_when_explicitly_allowed(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'rest');

        $allocation = $this->service->allocate($batch, $plot, [
            'allowed_statuses' => ['available', 'rest'],
        ]);

        $this->assertNotNull($allocation->id);
        $this->assertEquals($batch->id, $allocation->planting_batch_id);
        $this->assertEquals($plot->id, $allocation->plot_id);
    }

    public function test_allocation_rejects_allocated_area_greater_than_plot_area(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'available', 100);

        $this->expectException(AllocationException::class);
        $this->expectExceptionMessage('allocated area');

        $this->service->allocate($batch, $plot, [
            'allocated_area_m2' => 150,
        ]);
    }

    public function test_allocation_rejects_area_exceeds_plot_error_code(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'available', 100);

        try {
            $this->service->allocate($batch, $plot, [
                'allocated_area_m2' => 150,
            ]);
            $this->fail('Expected AllocationException not thrown');
        } catch (AllocationException $e) {
            $this->assertEquals(AllocationException::CODE_AREA_EXCEEDS_PLOT, $e->errorCode);
            $this->assertArrayHasKey('allocated_area_m2', $e->context);
            $this->assertArrayHasKey('plot_area_m2', $e->context);
        }
    }

    public function test_allocation_rejects_duplicate_batch_plot_allocation(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $this->service->allocate($batch, $plot);

        $this->expectException(AllocationException::class);
        $this->expectExceptionMessage('already has allocation');

        $this->service->allocate($batch, $plot);
    }

    public function test_allocation_rejects_duplicate_with_bed(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $bed = \App\Models\Bed::create([
            'plot_id' => $plot->id,
            'code' => 'BED-01',
            'length_m' => 10,
            'width_m' => 1,
            'area_m2' => 10,
            'expected_plants' => 100,
            'status' => 'available',
        ]);

        $this->service->allocate($batch, $plot, [], $bed->id);

        $this->expectException(AllocationException::class);

        $this->service->allocate($batch, $plot, [], $bed->id);
    }

    public function test_allocation_allows_different_beds_same_plot(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $bed1 = \App\Models\Bed::create([
            'plot_id' => $plot->id,
            'code' => 'BED-01',
            'length_m' => 10,
            'width_m' => 1,
            'area_m2' => 10,
            'expected_plants' => 100,
            'status' => 'available',
        ]);

        $bed2 = \App\Models\Bed::create([
            'plot_id' => $plot->id,
            'code' => 'BED-02',
            'length_m' => 10,
            'width_m' => 1,
            'area_m2' => 10,
            'expected_plants' => 100,
            'status' => 'available',
        ]);

        $allocation1 = $this->service->allocate($batch, $plot, [], $bed1->id);
        $allocation2 = $this->service->allocate($batch, $plot, [], $bed2->id);

        $this->assertNotNull($allocation1->id);
        $this->assertNotNull($allocation2->id);
        $this->assertEquals($bed1->id, $allocation1->bed_id);
        $this->assertEquals($bed2->id, $allocation2->bed_id);
    }

    public function test_allocation_rejects_duplicate_batch_plot_without_bed(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation1 = $this->service->allocate($batch, $plot, [], null);
        $this->assertNotNull($allocation1->id);

        $this->expectException(AllocationException::class);
        $this->expectExceptionMessage('already has allocation');

        $this->service->allocate($batch, $plot, [], null);
    }

    public function test_allocation_allows_multi_plot_same_farm(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);

        $plot1 = $this->createPlot($farm, 'available', 100);
        $plot2 = $this->createPlot($farm, 'available', 150);

        $allocation1 = $this->service->allocate($batch, $plot1, ['allocated_area_m2' => 100]);
        $allocation2 = $this->service->allocate($batch, $plot2, ['allocated_area_m2' => 150]);

        $this->assertNotNull($allocation1->id);
        $this->assertNotNull($allocation2->id);

        $batch->refresh();
        $this->assertCount(2, $batch->allocations);
    }

    public function test_allocation_updates_plot_current_batch_id(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $this->assertNull($plot->current_batch_id);

        $this->service->allocate($batch, $plot);

        $plot->refresh();
        $this->assertEquals($batch->id, $plot->current_batch_id);
    }

    public function test_allocation_exception_to_array(): void
    {
        $exception = AllocationException::farmMismatch(1, 2);
        $array = $exception->toArray();

        $this->assertEquals('allocation_error', $array['error']);
        $this->assertEquals(AllocationException::CODE_FARM_MISMATCH, $array['code']);
        $this->assertArrayHasKey('context', $array);
        $this->assertEquals(1, $array['context']['batch_farm_id']);
        $this->assertEquals(2, $array['context']['plot_farm_id']);
    }

    public function test_validate_only_does_not_create(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'available', 100);

        $this->service->validateAllocation($batch, $plot, [
            'allocated_area_m2' => 50,
        ]);

        $this->assertEquals(0, PlantingBatchAllocation::count());
    }

    public function test_validate_throws_on_farm_mismatch(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm1 = $this->createFarm('FA01');
        $farm2 = $this->createFarm('FA02');
        $crop = $this->createCrop();

        $batch = $this->createBatch($farm1, $crop);
        $plot = $this->createPlot($farm2);

        $this->expectException(AllocationException::class);
        $this->service->validateAllocation($batch, $plot);
    }

    public function test_validate_throws_on_duplicate(): void
    {
        Sanctum::actingAs(\App\Models\User::factory()->create(['role' => \App\Models\User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $this->service->allocate($batch, $plot);

        $this->expectException(AllocationException::class);
        $this->service->validateAllocation($batch, $plot);
    }
}