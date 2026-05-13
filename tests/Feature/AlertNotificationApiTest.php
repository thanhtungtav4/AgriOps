<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\User;
use App\Models\WorkTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlertNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Crop $crop;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create(['name' => 'Farm Alerts', 'code' => 'FA', 'status' => 'active']);
        $this->crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
        $this->manager = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_overdue_work_task_trigger_creates_alert_for_farm_manager_role(): void
    {
        Sanctum::actingAs($this->manager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'code' => 'TM-FA-2026-001',
            'status' => 'growing',
        ]);

        $task = WorkTask::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'title' => 'Morning irrigation',
            'task_type' => 'irrigation',
            'status' => 'planned',
            'priority' => 'high',
            'planned_due_date' => now()->toDateString(),
        ]);

        $this->postJson('/api/v1/alerts/trigger')
            ->assertCreated()
            ->assertJsonPath('data.created_or_updated', 1)
            ->assertJsonPath('data.alerts.0.alert_type', 'work_task_overdue')
            ->assertJsonPath('data.alerts.0.recipient_role', User::ROLE_FARM_MANAGER)
            ->assertJsonPath('data.alerts.0.context.task_id', $task->id)
            ->assertJsonPath('data.alerts.0.notification_payload.channel', 'in_app_push_ready');

        $this->getJson('/api/v1/alerts')
            ->assertOk()
            ->assertJsonPath('data.0.alert_type', 'work_task_overdue')
            ->assertJsonPath('data.0.context.task_title', 'Morning irrigation')
            ->assertJsonPath('data.0.notification_payload.recipient.role', User::ROLE_FARM_MANAGER);
    }

    public function test_yield_shortfall_trigger_creates_contextual_alert(): void
    {
        Sanctum::actingAs($this->manager);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'code' => 'TM-FA-2026-002',
            'status' => 'harvesting',
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'actual_quantity' => 50,
        ]);

        $this->postJson('/api/v1/alerts/trigger')
            ->assertCreated()
            ->assertJsonPath('data.created_or_updated', 1)
            ->assertJsonPath('data.alerts.0.alert_type', 'yield_shortfall')
            ->assertJsonPath('data.alerts.0.context.batch_id', $batch->id)
            ->assertJsonPath('data.alerts.0.context.crop_id', $this->crop->id)
            ->assertJsonPath('data.alerts.0.context.gap_quantity', 50)
            ->assertJsonPath('data.alerts.0.context.unit', 'kg');
    }

    public function test_alert_can_be_marked_read(): void
    {
        Sanctum::actingAs($this->manager);

        WorkTask::create([
            'farm_id' => $this->farm->id,
            'title' => 'Late task',
            'status' => 'planned',
            'planned_due_date' => now()->toDateString(),
        ]);

        $alertId = $this->postJson('/api/v1/alerts/trigger')->json('data.alerts.0.id');

        $this->patchJson("/api/v1/alerts/{$alertId}/read")
            ->assertOk()
            ->assertJsonPath('data.status', 'read')
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
    }
}
