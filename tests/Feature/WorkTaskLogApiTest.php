<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\FarmingLog;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkTaskLogApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Farm $otherFarm;
    private User $worker;
    private Crop $crop;
    private PlantingBatch $batch;
    private Plot $plot;

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

        $this->worker = User::create([
            'name' => 'Worker',
            'email' => 'worker-log@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_WORKER,
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
            'status' => 'planned',
            'planned_start_date' => now()->toDateString(),
        ]);

        $this->plot = Plot::create([
            'name' => 'Plot A',
            'code' => 'PLOT-A',
            'farm_id' => $this->farm->id,
            'area_m2' => 100,
            'status' => 'available',
        ]);
    }

    public function test_worker_can_submit_log_with_photo_paths(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'assigned_user_id' => $this->worker->id,
            'title' => 'Irrigate bed',
            'status' => 'in_progress',
            'priority' => 'normal',
            'started_at' => now()->subHour(),
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'actual_start_at' => now()->subHour()->toIso8601String(),
            'actual_end_at' => now()->toIso8601String(),
            'notes' => 'Watered according to task.',
            'photo_paths' => ['/storage/task-photos/irrigate-1.jpg'],
            'metadata' => ['water_liters' => 30],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.work_task_id', $task->id)
            ->assertJsonPath('data.farm_id', $this->farm->id)
            ->assertJsonPath('data.reported_by_user_id', $this->worker->id)
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.plot_id', $this->plot->id)
            ->assertJsonPath('data.photo_paths.0', '/storage/task-photos/irrigate-1.jpg');

        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertEquals('Watered according to task.', $task->completion_note);
    }

    public function test_log_prefills_task_context_without_client_repeating_known_fields(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'assigned_user_id' => $this->worker->id,
            'title' => 'Check plants',
            'status' => 'assigned',
            'priority' => 'normal',
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'notes' => 'Checked row one.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.farm_id', $this->farm->id)
            ->assertJsonPath('data.work_task_id', $task->id)
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.plot_id', $this->plot->id)
            ->assertJsonPath('data.reported_by_user_id', $this->worker->id);
    }

    public function test_client_uuid_makes_work_task_log_submission_idempotent(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'assigned_user_id' => $this->worker->id,
            'title' => 'Offline queued task',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $payload = [
            'client_uuid' => 'offline-local-123',
            'notes' => 'Submitted from offline queue.',
            'photo_paths' => ['/storage/task-photos/offline-1.jpg'],
        ];

        $firstResponse = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', $payload);
        $secondResponse = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', $payload);

        $firstResponse->assertStatus(201)
            ->assertJsonPath('data.client_uuid', 'offline-local-123');

        $secondResponse->assertStatus(200)
            ->assertJsonPath('data.id', $firstResponse->json('data.id'))
            ->assertJsonPath('data.client_uuid', 'offline-local-123');

        $this->assertEquals(1, FarmingLog::where('work_task_id', $task->id)->count());
    }

    public function test_worker_can_submit_log_with_uploaded_photos(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'assigned_user_id' => $this->worker->id,
            'title' => 'Photo upload task',
            'status' => 'in_progress',
            'priority' => 'normal',
            'metadata' => ['requires_photo' => true],
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/work-tasks/' . $task->id . '/logs', [
            'notes' => 'Uploaded actual field photo.',
            'photos' => [
                UploadedFile::fake()->image('field-photo.jpg', 800, 600),
            ],
        ]);

        $response->assertStatus(201);

        $photoPath = $response->json('data.photo_paths.0');
        $this->assertStringStartsWith('/storage/work-task-logs/' . $task->id . '/', $photoPath);

        Storage::disk('public')->assertExists(str_replace('/storage/', '', $photoPath));
    }

    public function test_required_photo_task_rejects_log_without_photo_paths(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Photo required task',
            'status' => 'in_progress',
            'priority' => 'normal',
            'metadata' => ['requires_photo' => true],
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'notes' => 'No photo here.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_REQUIRED')
            ->assertJsonPath('error.details.field', 'photo_paths');
    }

    public function test_uploaded_photo_must_be_an_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Invalid upload task',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/work-tasks/' . $task->id . '/logs', [
            'photos' => [
                UploadedFile::fake()->create('field-note.pdf', 10, 'application/pdf'),
            ],
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, FarmingLog::count());
    }

    public function test_worker_cannot_submit_log_for_another_farm_task(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->otherFarm->id,
            'title' => 'Other farm task',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'notes' => 'Should fail.',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_submit_log_for_cancelled_task(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Cancelled task',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'notes' => 'Should fail.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'TASK_CANCELLED');
    }

    public function test_actual_end_must_be_after_actual_start(): void
    {
        Sanctum::actingAs($this->worker);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Bad time task',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $response = $this->postJson('/api/v1/work-tasks/' . $task->id . '/logs', [
            'actual_start_at' => '2026-05-13T10:00:00+07:00',
            'actual_end_at' => '2026-05-13T09:00:00+07:00',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, FarmingLog::count());
    }
}
