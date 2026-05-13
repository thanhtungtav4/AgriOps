<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use App\Models\User;
use App\Services\PlantingBatchAllocationService;
use App\Services\PlantingBatchLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private PlantingBatchLifecycleService $lifecycleService;
    private PlantingBatchAllocationService $allocationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lifecycleService = new PlantingBatchLifecycleService();
        $this->allocationService = new PlantingBatchAllocationService();
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

    private function createBatch(Farm $farm, Crop $crop, string $status = 'planned'): PlantingBatch
    {
        return PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => $status,
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

    public function test_lifecycle_transition_creates_audit_event(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop, 'planned');

        $this->assertEquals(0, AuditEvent::count());

        $this->lifecycleService->transition($batch, 'approved');

        $this->assertEquals(1, AuditEvent::count());
        $event = AuditEvent::first();
        $this->assertEquals(AuditEvent::TYPE_LIFECYCLE_TRANSITION, $event->event_type);
        $this->assertEquals('PlantingBatch', $event->entity_type);
        $this->assertEquals($batch->id, $event->entity_id);
        $this->assertEquals($farm->id, $event->farm_id);
        $this->assertEquals('planned', $event->from_status);
        $this->assertEquals('approved', $event->to_status);
        $this->assertEquals('system', $event->actor_type);
    }

    public function test_lifecycle_transition_records_user_id_when_provided(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FARM_MANAGER]);
        Sanctum::actingAs($user);

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop, 'planned');

        $this->lifecycleService->transition($batch, 'approved', null, $user->id);

        $event = AuditEvent::first();
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals('user', $event->actor_type);
    }

    public function test_lifecycle_transition_cancellation_includes_reason(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop, 'approved');

        $this->lifecycleService->transition($batch, 'cancelled', 'Weather conditions unfavorable for planting');

        $event = AuditEvent::first();
        $this->assertEquals('approved', $event->from_status);
        $this->assertEquals('cancelled', $event->to_status);
        $this->assertEquals('Weather conditions unfavorable for planting', $event->reason);
        $this->assertNotNull($event->metadata);
    }

    public function test_multiple_lifecycle_transitions_create_multiple_events(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop, 'planned');

        $this->lifecycleService->transition($batch, 'approved');
        $batch->refresh();
        $this->lifecycleService->transition($batch, 'soil_prep');
        $batch->refresh();
        $this->lifecycleService->transition($batch, 'planting');

        $this->assertEquals(3, AuditEvent::count());
        $events = AuditEvent::orderBy('id')->get();
        $this->assertEquals('planned', $events[0]->from_status);
        $this->assertEquals('approved', $events[0]->to_status);
        $this->assertEquals('approved', $events[1]->from_status);
        $this->assertEquals('soil_prep', $events[1]->to_status);
        $this->assertEquals('soil_prep', $events[2]->from_status);
        $this->assertEquals('planting', $events[2]->to_status);
    }

    public function test_allocation_creates_audit_event(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $this->assertEquals(0, AuditEvent::count());

        $allocation = $this->allocationService->allocate($batch, $plot);

        $this->assertEquals(1, AuditEvent::count());
        $event = AuditEvent::first();
        $this->assertEquals(AuditEvent::TYPE_ALLOCATION_CREATED, $event->event_type);
        $this->assertEquals('PlantingBatchAllocation', $event->entity_type);
        $this->assertEquals($allocation->id, $event->entity_id);
        $this->assertEquals($farm->id, $event->farm_id);
        $this->assertEquals($plot->id, $event->plot_id);
        $this->assertEquals('system', $event->actor_type);
    }

    public function test_allocation_records_user_id_when_provided(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FARM_MANAGER]);
        Sanctum::actingAs($user);

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $this->allocationService->allocate($batch, $plot, [], null, $user->id);

        $event = AuditEvent::first();
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals('user', $event->actor_type);
    }

    public function test_allocation_with_bed_records_bed_id(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

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

        $allocation = $this->allocationService->allocate($batch, $plot, [], $bed->id);

        $event = AuditEvent::first();
        $this->assertEquals($bed->id, $event->bed_id);
    }

    public function test_allocation_records_allocated_area(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'available', 200);

        $this->allocationService->allocate($batch, $plot, ['allocated_area_m2' => 150]);

        $event = AuditEvent::first();
        $this->assertEquals(150, $event->allocated_area_m2);
    }

    public function test_multiple_allocations_create_multiple_events(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot1 = $this->createPlot($farm);
        $plot2 = $this->createPlot($farm);

        $this->allocationService->allocate($batch, $plot1);
        $this->allocationService->allocate($batch, $plot2);

        $this->assertEquals(2, AuditEvent::count());
        $events = AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_CREATED)->get();
        $this->assertCount(2, $events);
    }

    public function test_audit_event_has_proper_relationships(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop, 'planned');

        $this->lifecycleService->transition($batch, 'approved');

        $event = AuditEvent::first();
        $this->assertNotNull($event->farm);
        $this->assertEquals($farm->id, $event->farm->id);
    }

    public function test_deallocation_creates_audit_event(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation = $this->allocationService->allocate($batch, $plot);
        $this->assertEquals(1, AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_CREATED)->count());

        $this->allocationService->deallocate($allocation);

        $this->assertEquals(1, AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->count());
        $event = AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->first();
        $this->assertEquals(AuditEvent::TYPE_ALLOCATION_REMOVED, $event->event_type);
        $this->assertEquals('PlantingBatchAllocation', $event->entity_type);
        $this->assertEquals($allocation->id, $event->entity_id);
        $this->assertEquals($farm->id, $event->farm_id);
        $this->assertEquals($plot->id, $event->plot_id);
        $this->assertEquals('system', $event->actor_type);
    }

    public function test_deallocation_records_user_id_when_provided(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_FARM_MANAGER]);
        Sanctum::actingAs($user);

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation = $this->allocationService->allocate($batch, $plot);
        $this->allocationService->deallocate($allocation, null, $user->id);

        $event = AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->first();
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals('user', $event->actor_type);
    }

    public function test_deallocation_records_reason(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation = $this->allocationService->allocate($batch, $plot);
        $this->allocationService->deallocate($allocation, 'Plot no longer available for planting');

        $event = AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->first();
        $this->assertEquals('Plot no longer available for planting', $event->reason);
    }

    public function test_deallocation_clears_plot_current_batch_id_when_last(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation = $this->allocationService->allocate($batch, $plot);
        $plot->refresh();
        $this->assertEquals($batch->id, $plot->current_batch_id);

        $this->allocationService->deallocate($allocation);
        $plot->refresh();
        $this->assertNull($plot->current_batch_id);
    }

    public function test_deallocation_preserves_plot_current_batch_when_other_allocations_exist(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm);

        $allocation1 = $this->allocationService->allocate($batch, $plot);
        $bed = \App\Models\Bed::create([
            'plot_id' => $plot->id,
            'code' => 'BED-01',
            'length_m' => 10,
            'width_m' => 1,
            'area_m2' => 10,
            'expected_plants' => 100,
            'status' => 'available',
        ]);
        $allocation2 = $this->allocationService->allocate($batch, $plot, [], $bed->id);

        $plot->refresh();
        $this->assertEquals($batch->id, $plot->current_batch_id);

        $this->allocationService->deallocate($allocation1);
        $plot->refresh();
        $this->assertEquals($batch->id, $plot->current_batch_id);

        $this->allocationService->deallocate($allocation2);
        $plot->refresh();
        $this->assertNull($plot->current_batch_id);
    }

    public function test_multiple_allocations_and_deallocations_create_correct_events(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot1 = $this->createPlot($farm);
        $plot2 = $this->createPlot($farm);

        $allocation1 = $this->allocationService->allocate($batch, $plot1);
        $allocation2 = $this->allocationService->allocate($batch, $plot2);

        $this->assertEquals(2, AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_CREATED)->count());

        $this->allocationService->deallocate($allocation1);
        $this->assertEquals(1, AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->count());

        $this->allocationService->deallocate($allocation2);
        $this->assertEquals(2, AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->count());
    }

    public function test_allocation_removed_event_includes_allocated_area(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $crop = $this->createCrop();
        $batch = $this->createBatch($farm, $crop);
        $plot = $this->createPlot($farm, 'available', 200);

        $allocation = $this->allocationService->allocate($batch, $plot, ['allocated_area_m2' => 150]);
        $this->allocationService->deallocate($allocation);

        $event = AuditEvent::where('event_type', AuditEvent::TYPE_ALLOCATION_REMOVED)->first();
        $this->assertEquals(150, $event->allocated_area_m2);
    }
}