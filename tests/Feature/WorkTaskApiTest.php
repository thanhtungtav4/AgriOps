<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\GrowthStage;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\User;
use App\Models\WorkTask;
use App\Models\Plot;
use App\Models\Bed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkTaskApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Farm $otherFarm;
    private User $adminUser;
    private User $farmManager;
    private User $worker;
    private Crop $crop;
    private Plot $plot;
    private Bed $bed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'FARM001',
            'status' => 'active',
        ]);

        $this->otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'FARM002',
            'status' => 'active',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'farm_id' => null,
        ]);

        $this->farmManager = User::create([
            'name' => 'Farm Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'farm_manager',
            'farm_id' => $this->farm->id,
        ]);

        $this->worker = User::create([
            'name' => 'Worker',
            'email' => 'worker@example.com',
            'password' => bcrypt('password'),
            'role' => 'worker',
            'farm_id' => $this->farm->id,
        ]);

        $this->crop = Crop::create([
            'name' => 'Tomato',
            'code' => 'TOM',
        ]);

        $this->plot = Plot::create([
            'name' => 'Plot A',
            'code' => 'PLOT-A',
            'farm_id' => $this->farm->id,
            'status' => 'available',
        ]);

        $this->bed = Bed::create([
            'plot_id' => $this->plot->id,
            'name' => 'Bed 1',
            'code' => 'BED-1',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Soil Preparation',
            'code' => 'soil_prep',
            'order' => 1,
            'duration_days' => 7,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Planting',
            'code' => 'planting',
            'order' => 2,
            'duration_days' => 1,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Growing',
            'code' => 'growing',
            'order' => 3,
            'duration_days' => 30,
        ]);
    }

    public function test_worker_can_list_only_own_farm_tasks(): void
    {
        Sanctum::actingAs($this->worker);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Own Farm Task',
            'status' => 'planned',
            'priority' => 'normal',
            'planned_due_date' => now()->addDays(1),
        ]);

        WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Other Farm Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Own Farm Task');
    }

    public function test_admin_can_list_all_farm_tasks(): void
    {
        Sanctum::actingAs($this->adminUser);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Farm 1 Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Farm 2 Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_list_includes_batch_crop_and_plot_context(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now(),
        ]);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'plot_id' => $this->plot->id,
            'bed_id' => $this->bed->id,
            'title' => 'Task with Context',
            'status' => 'planned',
            'priority' => 'normal',
            'planned_due_date' => now()->addDays(5),
        ]);

        $response = $this->getJson('/api/v1/work-tasks');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Task with Context')
            ->assertJsonPath('data.0.planting_batch.crop.name', 'Tomato')
            ->assertJsonPath('data.0.plot.name', 'Plot A');
    }

    public function test_list_filter_by_status(): void
    {
        Sanctum::actingAs($this->farmManager);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Planned Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Done Task',
            'status' => 'done',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks?status=planned');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'planned');
    }

    public function test_list_filter_by_assigned_user(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task1 = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Assigned Task',
            'status' => 'assigned',
            'assigned_user_id' => $this->worker->id,
            'priority' => 'normal',
        ]);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Unassigned Task',
            'status' => 'planned',
            'assigned_user_id' => null,
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks?assigned_user_id=' . $this->worker->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.assigned_user_id', $this->worker->id);
    }

    public function test_list_filter_by_planting_batch(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
        ]);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Batch Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => null,
            'title' => 'Non-Batch Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks?planting_batch_id=' . $batch->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.planting_batch_id', $batch->id);
    }

    public function test_list_filter_by_due_before(): void
    {
        Sanctum::actingAs($this->farmManager);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Due Soon',
            'status' => 'planned',
            'planned_due_date' => now()->addDays(2),
            'priority' => 'normal',
        ]);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Due Later',
            'status' => 'planned',
            'planned_due_date' => now()->addDays(10),
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks?due_before=' . now()->addDays(5)->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Due Soon');
    }

    public function test_list_default_order(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task1 = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task B',
            'status' => 'planned',
            'planned_due_date' => now()->addDays(2),
            'priority' => 'normal',
        ]);

        $task2 = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task A',
            'status' => 'planned',
            'planned_due_date' => now()->addDays(1),
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Task A', $data[0]['title']);
        $this->assertEquals('Task B', $data[1]['title']);
    }

    public function test_user_can_view_own_farm_task(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Viewable Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks/' . $task->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Viewable Task');
    }

    public function test_user_cannot_view_another_farm_task(): void
    {
        Sanctum::actingAs($this->worker);

        $otherFarmTask = WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Other Farm Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks/' . $otherFarmTask->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_task(): void
    {
        Sanctum::actingAs($this->adminUser);

        $task = WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Admin Viewable Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->getJson('/api/v1/work-tasks/' . $task->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Admin Viewable Task');
    }

    public function test_show_returns_404_for_nonexistent_task(): void
    {
        Sanctum::actingAs($this->farmManager);

        $response = $this->getJson('/api/v1/work-tasks/99999');

        $response->assertStatus(404);
    }

    public function test_batch_generate_creates_tasks_from_growth_stages(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/v1/planting-batches/' . $batch->id . '/generate-work-tasks');

        $response->assertStatus(201)
            ->assertJsonPath('meta.created_count', 3)
            ->assertJsonStructure([
                'data' => [
                    'tasks' => [
                        '*' => ['title', 'status', 'planned_due_date']
                    ]
                ],
                'meta' => ['created_count', 'existing_count', 'skipped_count']
            ]);
    }

    public function test_generate_endpoint_is_idempotent(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $this->postJson('/api/v1/planting-batches/' . $batch->id . '/generate-work-tasks');

        $response = $this->postJson('/api/v1/planting-batches/' . $batch->id . '/generate-work-tasks');

        $response->assertStatus(200)
            ->assertJsonPath('meta.created_count', 0)
            ->assertJsonPath('meta.existing_count', 3)
            ->assertJsonPath('meta.skipped_count', 3);
    }

    public function test_generate_with_allocation_id(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $batch->id,
            'plot_id' => $this->plot->id,
            'bed_id' => $this->bed->id,
            'allocated_area_m2' => 100,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/planting-batches/' . $batch->id . '/generate-work-tasks', [
            'allocation_id' => $allocation->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('meta.created_count', 3);

        $tasks = $response->json('data.tasks');
        foreach ($tasks as $task) {
            $this->assertEquals($allocation->id, $task['planting_batch_allocation_id']);
        }
    }

    public function test_generate_rejects_allocation_from_another_batch(): void
    {
        Sanctum::actingAs($this->farmManager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $otherBatch->id,
            'plot_id' => $this->plot->id,
            'bed_id' => $this->bed->id,
            'allocated_area_m2' => 100,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/planting-batches/' . $batch->id . '/generate-work-tasks', [
            'allocation_id' => $allocation->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_ALLOCATION');
    }

    public function test_generate_requires_existing_batch(): void
    {
        Sanctum::actingAs($this->farmManager);

        $response = $this->postJson('/api/v1/planting-batches/99999/generate-work-tasks');

        $response->assertStatus(404);
    }

    public function test_planned_to_assigned_requires_assigned_user_id(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Assign',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'assigned',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_REQUIRED');
    }

    public function test_planned_to_assigned_success(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Assign',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'assigned',
            'assigned_user_id' => $this->worker->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.assigned_user_id', $this->worker->id);
    }

    public function test_assignment_rejects_user_from_another_farm(): void
    {
        Sanctum::actingAs($this->farmManager);

        $otherFarmWorker = User::create([
            'name' => 'Other Worker',
            'email' => 'other-worker@example.com',
            'password' => bcrypt('password'),
            'role' => 'worker',
            'farm_id' => $this->otherFarm->id,
        ]);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Assign',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'assigned',
            'assigned_user_id' => $otherFarmWorker->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_ASSIGNEE');
    }

    public function test_assigned_to_in_progress_sets_started_at(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Start',
            'status' => 'assigned',
            'assigned_user_id' => $this->worker->id,
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');
        $this->assertNotNull($response->json('data.started_at'));
    }

    public function test_in_progress_to_done_sets_completed_at(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Complete',
            'status' => 'in_progress',
            'assigned_user_id' => $this->worker->id,
            'started_at' => now(),
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'done',
            'completion_note' => 'Completed successfully',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'done');
        $this->assertNotNull($response->json('data.completed_at'));
        $this->assertEquals('Completed successfully', $response->json('data.completion_note'));
    }

    public function test_planned_to_cancelled_requires_reason(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Cancel',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_REQUIRED');
    }

    public function test_cancelled_success(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Task to Cancel',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'cancelled',
            'reason' => 'Weather conditions',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_invalid_transition_returns_422(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Invalid Transition Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_done_is_terminal(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Done Task',
            'status' => 'done',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'cancelled',
            'reason' => 'Should not work',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_cancelled_is_terminal(): void
    {
        Sanctum::actingAs($this->farmManager);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Cancelled Task',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'assigned',
            'assigned_user_id' => $this->worker->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_user_cannot_update_another_farm_task_status(): void
    {
        Sanctum::actingAs($this->worker);

        $otherFarmTask = WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Other Farm Task',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $otherFarmTask->id . '/status', [
            'status' => 'cancelled',
            'reason' => 'Testing',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_task_status(): void
    {
        Sanctum::actingAs($this->adminUser);

        $task = WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Admin Task Update',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->patchJson('/api/v1/work-tasks/' . $task->id . '/status', [
            'status' => 'cancelled',
            'reason' => 'Admin cancellation',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }
}
