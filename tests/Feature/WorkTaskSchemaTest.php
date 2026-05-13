<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\GrowthStage;
use App\Models\Plot;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\User;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkTaskSchemaTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // RED Phase: Tests that verify WorkTask model schema works
    // ============================================================

    public function test_work_task_can_be_created_with_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Prepare soil for planting',
            'task_type' => 'soil_prep',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->id);
        $this->assertEquals($farm->id, $task->farm_id);
        $this->assertEquals('Prepare soil for planting', $task->title);
        $this->assertEquals('soil_prep', $task->task_type);
    }

    public function test_work_task_defaults_status_to_planned(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
        ]);

        $this->assertEquals('planned', $task->status);
    }

    public function test_work_task_defaults_priority_to_normal(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
        ]);

        $this->assertEquals('normal', $task->priority);
    }

    public function test_work_task_belongs_to_farm(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->farm);
        $this->assertEquals($farm->id, $task->farm->id);
    }

    public function test_work_task_belongs_to_planting_batch(): void
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

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Prepare seedbed',
            'task_type' => 'soil_prep',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->plantingBatch);
        $this->assertEquals($batch->id, $task->plantingBatch->id);
    }

    public function test_work_task_can_link_to_planting_batch_allocation(): void
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

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'planting_batch_allocation_id' => $allocation->id,
            'title' => 'Water allocation area',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->allocation);
        $this->assertEquals($allocation->id, $task->allocation->id);
    }

    public function test_work_task_can_link_to_plot(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'plot_id' => $plot->id,
            'title' => 'Inspect plot',
            'task_type' => 'inspection',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->plot);
        $this->assertEquals($plot->id, $task->plot->id);
    }

    public function test_work_task_can_link_to_bed(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $plot = Plot::create([
            'farm_id' => $farm->id,
            'code' => 'PLOT-A',
            'name' => 'Plot A',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $bed = Bed::create([
            'plot_id' => $plot->id,
            'code' => 'BED-1',
            'name' => 'Bed 1',
            'area_m2' => 10,
            'position' => 1,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'plot_id' => $plot->id,
            'bed_id' => $bed->id,
            'title' => 'Plant seedlings in bed',
            'task_type' => 'planting',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->bed);
        $this->assertEquals($bed->id, $task->bed->id);
    }

    public function test_work_task_can_link_to_growth_stage(): void
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

        $growthStage = GrowthStage::create([
            'crop_id' => $crop->id,
            'name' => 'Seedling',
            'code' => 'SEEDLING',
            'order' => 1,
            'duration_days' => 14,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => null,
            'growth_stage_id' => $growthStage->id,
            'title' => 'Apply fertilizer for seedling stage',
            'task_type' => 'fertilizing',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->growthStage);
        $this->assertEquals($growthStage->id, $task->growthStage->id);
    }

    public function test_work_task_can_be_assigned_to_user(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $worker = User::factory()->create([
            'role' => User::ROLE_WORKER,
            'farm_id' => $farm->id,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'assigned_user_id' => $worker->id,
            'title' => 'Harvest tomatoes',
            'task_type' => 'harvesting',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        $this->assertNotNull($task->assignedUser);
        $this->assertEquals($worker->id, $task->assignedUser->id);
    }

    public function test_work_task_has_planned_dates(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task with dates',
            'task_type' => 'irrigation',
            'planned_start_date' => '2026-06-01',
            'planned_due_date' => '2026-06-05',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertNotNull($task->planned_start_date);
        $this->assertEquals('2026-06-01', $task->planned_start_date->format('Y-m-d'));
        $this->assertNotNull($task->planned_due_date);
        $this->assertEquals('2026-06-05', $task->planned_due_date->format('Y-m-d'));
    }

    public function test_work_task_has_actual_timestamps(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task with timestamps',
            'task_type' => 'irrigation',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $task->update([
            'started_at' => '2026-06-01 08:00:00',
            'completed_at' => '2026-06-01 10:30:00',
            'status' => 'done',
        ]);

        $task->refresh();

        $this->assertNotNull($task->started_at);
        $this->assertNotNull($task->completed_at);
        $this->assertEquals('done', $task->status);
    }

    public function test_planned_and_actual_timestamps_remain_separate(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'planned_start_date' => '2026-06-01',
            'planned_due_date' => '2026-06-05',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertEquals('2026-06-01', $task->planned_start_date->format('Y-m-d'));
        $this->assertEquals('2026-06-05', $task->planned_due_date->format('Y-m-d'));
        $this->assertNull($task->started_at);
        $this->assertNull($task->completed_at);

        $task->update([
            'started_at' => '2026-06-01 08:00:00',
            'completed_at' => '2026-06-01 10:30:00',
            'status' => 'done',
        ]);

        $task->refresh();

        $this->assertEquals('2026-06-01', $task->planned_start_date->format('Y-m-d'));
        $this->assertEquals('2026-06-05', $task->planned_due_date->format('Y-m-d'));
        $this->assertNotNull($task->started_at);
        $this->assertNotNull($task->completed_at);
    }

    public function test_work_task_has_instructions_and_completion_note(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Apply pesticide',
            'task_type' => 'pesticide',
            'instructions' => 'Mix 50ml per 10L water. Apply in early morning.',
            'status' => 'planned',
            'priority' => 'urgent',
        ]);

        $this->assertEquals('Mix 50ml per 10L water. Apply in early morning.', $task->instructions);

        $task->update([
            'completion_note' => 'Applied 8L solution. Weather was clear.',
            'status' => 'done',
        ]);

        $task->refresh();
        $this->assertEquals('Applied 8L solution. Weather was clear.', $task->completion_note);
    }

    public function test_work_task_has_metadata_json(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'title' => 'Test task with metadata',
            'task_type' => 'irrigation',
            'metadata' => ['irrigation_type' => 'drip', 'duration_minutes' => 30],
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $this->assertIsArray($task->metadata);
        $this->assertEquals('drip', $task->metadata['irrigation_type']);
        $this->assertEquals(30, $task->metadata['duration_minutes']);
    }

    public function test_work_task_status_enum_values(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $validStatuses = ['planned', 'assigned', 'in_progress', 'done', 'cancelled'];

        foreach ($validStatuses as $status) {
            $task = WorkTask::create([
                'farm_id' => $farm->id,
                'title' => "Test task status $status",
                'task_type' => 'irrigation',
                'status' => $status,
                'priority' => 'normal',
            ]);

            $this->assertEquals($status, $task->status);
        }
    }

    public function test_work_task_priority_enum_values(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $validPriorities = ['low', 'normal', 'high', 'urgent'];

        foreach ($validPriorities as $priority) {
            $task = WorkTask::create([
                'farm_id' => $farm->id,
                'title' => "Test task priority $priority",
                'task_type' => 'irrigation',
                'priority' => $priority,
                'status' => 'planned',
            ]);

            $this->assertEquals($priority, $task->priority);
        }
    }

    // ============================================================
    // FK Protection Tests
    // ============================================================

    public function test_work_task_cascade_deletes_when_batch_deleted(): void
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

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $taskId = $task->id;
        $batch->delete();

        $this->assertNull(WorkTask::find($taskId));
    }

    public function test_work_task_nullifies_when_allocation_deleted(): void
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

        $task = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'planting_batch_allocation_id' => $allocation->id,
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $allocation->delete();
        $task->refresh();

        $this->assertNull($task->planting_batch_allocation_id);
    }

    // ============================================================
    // Relationship Tests - Batch has many WorkTasks
    // ============================================================

    public function test_planting_batch_has_many_work_tasks(): void
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

        $task1 = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Task 1',
            'task_type' => 'soil_prep',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $task2 = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Task 2',
            'task_type' => 'planting',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $batch->refresh();
        $this->assertCount(2, $batch->workTasks);
        $this->assertTrue($batch->workTasks->contains($task1));
        $this->assertTrue($batch->workTasks->contains($task2));
    }

    public function test_planting_batch_allocation_has_many_work_tasks(): void
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

        $task1 = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'planting_batch_allocation_id' => $allocation->id,
            'title' => 'Task 1',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $task2 = WorkTask::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'planting_batch_allocation_id' => $allocation->id,
            'title' => 'Task 2',
            'task_type' => 'fertilizing',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $allocation->refresh();
        $this->assertCount(2, $allocation->workTasks);
        $this->assertTrue($allocation->workTasks->contains($task1));
        $this->assertTrue($allocation->workTasks->contains($task2));
    }

    // ============================================================
    // Index Tests
    // ============================================================

    public function test_work_task_model_has_relationships(): void
    {
        $task = new WorkTask();

        $this->assertTrue(method_exists($task, 'farm'));
        $this->assertTrue(method_exists($task, 'plantingBatch'));
        $this->assertTrue(method_exists($task, 'allocation'));
        $this->assertTrue(method_exists($task, 'plot'));
        $this->assertTrue(method_exists($task, 'bed'));
        $this->assertTrue(method_exists($task, 'growthStage'));
        $this->assertTrue(method_exists($task, 'assignedUser'));
    }
}
