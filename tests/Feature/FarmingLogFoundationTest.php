<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\FarmingLog;
use App\Models\Plot;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\User;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FarmingLogFoundationTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // RED Phase: Tests that verify FarmingLog model schema works
    // ============================================================

    public function test_farming_log_can_be_created_with_required_fields(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->id);
        $this->assertEquals($farm->id, $log->farm_id);
        $this->assertEquals($task->id, $log->work_task_id);
    }

    public function test_farming_log_defaults_status_to_submitted(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
        ]);

        $this->assertEquals('submitted', $log->status);
    }

    public function test_farming_log_has_json_cast_on_photo_paths(): void
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

        $photoPaths = [
            '/storage/photos/task1_1.jpg',
            '/storage/photos/task1_2.jpg',
        ];

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
            'photo_paths' => $photoPaths,
        ]);

        $this->assertIsArray($log->photo_paths);
        $this->assertCount(2, $log->photo_paths);
        $this->assertEquals('/storage/photos/task1_1.jpg', $log->photo_paths[0]);
    }

    public function test_farming_log_has_json_cast_on_metadata(): void
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

        $metadata = [
            'weather' => 'sunny',
            'temperature' => 28,
            'humidity' => 75,
        ];

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
            'metadata' => $metadata,
        ]);

        $this->assertIsArray($log->metadata);
        $this->assertEquals('sunny', $log->metadata['weather']);
        $this->assertEquals(28, $log->metadata['temperature']);
    }

    public function test_farming_log_belongs_to_farm(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->farm);
        $this->assertEquals($farm->id, $log->farm->id);
    }

    public function test_farming_log_belongs_to_work_task(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->workTask);
        $this->assertEquals($task->id, $log->workTask->id);
    }

    public function test_farming_log_belongs_to_reported_by_user(): void
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
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'reported_by_user_id' => $worker->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->reportedByUser);
        $this->assertEquals($worker->id, $log->reportedByUser->id);
    }

    public function test_farming_log_belongs_to_planting_batch(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'planting_batch_id' => $batch->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->plantingBatch);
        $this->assertEquals($batch->id, $log->plantingBatch->id);
    }

    public function test_farming_log_belongs_to_plot(): void
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
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'plot_id' => $plot->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->plot);
        $this->assertEquals($plot->id, $log->plot->id);
    }

    public function test_farming_log_belongs_to_bed(): void
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
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'bed_id' => $bed->id,
            'logged_at' => now(),
        ]);

        $this->assertNotNull($log->bed);
        $this->assertEquals($bed->id, $log->bed->id);
    }

    public function test_farming_log_has_datetime_cast_on_logged_at(): void
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

        $loggedAt = '2026-05-10 14:30:00';

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => $loggedAt,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->logged_at);
        $this->assertEquals('2026-05-10', $log->logged_at->format('Y-m-d'));
    }

    public function test_farming_log_has_actual_timestamps(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
            'actual_start_at' => '2026-05-10 08:00:00',
            'actual_end_at' => '2026-05-10 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->actual_start_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->actual_end_at);
        $this->assertEquals('2026-05-10', $log->actual_start_at->format('Y-m-d'));
        $this->assertEquals('2026-05-10', $log->actual_end_at->format('Y-m-d'));
    }

    public function test_farming_log_status_enum_values(): void
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

        $validStatuses = ['draft', 'submitted', 'approved', 'rejected'];

        foreach ($validStatuses as $status) {
            $log = FarmingLog::create([
                'farm_id' => $farm->id,
                'work_task_id' => $task->id,
                'logged_at' => now(),
                'status' => $status,
            ]);

            $this->assertEquals($status, $log->status);
        }
    }

    // ============================================================
    // FK Protection Tests
    // ============================================================

    public function test_farming_log_cascade_deletes_when_work_task_deleted(): void
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

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
        ]);

        $logId = $log->id;
        $task->delete();

        $this->assertNull(FarmingLog::find($logId));
    }

    public function test_farming_log_nullifies_when_allocation_deleted(): void
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
            'title' => 'Test task',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $log = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'planting_batch_allocation_id' => $allocation->id,
            'logged_at' => now(),
        ]);

        $allocation->delete();
        $log->refresh();

        $this->assertNull($log->planting_batch_allocation_id);
    }

    // ============================================================
    // Relationship Tests - WorkTask has many FarmingLogs
    // ============================================================

    public function test_work_task_has_many_farming_logs(): void
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

        $log1 = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now(),
            'status' => 'submitted',
        ]);

        $log2 = FarmingLog::create([
            'farm_id' => $farm->id,
            'work_task_id' => $task->id,
            'logged_at' => now()->addHour(),
            'status' => 'approved',
        ]);

        $task->refresh();
        $this->assertCount(2, $task->farmingLogs);
        $this->assertTrue($task->farmingLogs->contains($log1));
        $this->assertTrue($task->farmingLogs->contains($log2));
    }

    // ============================================================
    // Index Tests
    // ============================================================

    public function test_farming_log_model_has_relationships(): void
    {
        $log = new FarmingLog();

        $this->assertTrue(method_exists($log, 'farm'));
        $this->assertTrue(method_exists($log, 'workTask'));
        $this->assertTrue(method_exists($log, 'plantingBatch'));
        $this->assertTrue(method_exists($log, 'allocation'));
        $this->assertTrue(method_exists($log, 'plot'));
        $this->assertTrue(method_exists($log, 'bed'));
        $this->assertTrue(method_exists($log, 'reportedByUser'));
    }

    public function test_work_task_model_has_farming_logs_relationship(): void
    {
        $task = new WorkTask();

        $this->assertTrue(method_exists($task, 'farmingLogs'));
    }
}
