<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\PlantingBatch;
use App\Models\User;
use App\Models\WorkTask;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AlertService
{
    public function trigger(?int $farmId = null): Collection
    {
        return collect()
            ->merge($this->triggerOverdueWorkTaskAlerts($farmId))
            ->merge($this->triggerYieldShortfallAlerts($farmId));
    }

    public function triggerOverdueWorkTaskAlerts(?int $farmId = null, ?CarbonInterface $asOf = null): Collection
    {
        $asOf ??= now();

        return WorkTask::with(['plantingBatch.crop', 'farm'])
            ->whereNotNull('planned_due_date')
            ->whereDate('planned_due_date', '<=', $asOf->toDateString())
            ->whereIn('status', ['planned', 'assigned', 'in_progress'])
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(fn (WorkTask $task) => $this->createOrUpdateAlert([
                'farm_id' => $task->farm_id,
                'recipient_role' => User::ROLE_FARM_MANAGER,
                'alert_type' => 'work_task_overdue',
                'severity' => $task->priority === 'urgent' ? 'critical' : 'warning',
                'title' => 'Work task overdue',
                'message' => "Task '{$task->title}' is overdue for farm {$task->farm?->code}.",
                'source_type' => WorkTask::class,
                'source_id' => $task->id,
                'context' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'task_status' => $task->status,
                    'due_date' => $task->planned_due_date?->toDateString(),
                    'crop_id' => $task->plantingBatch?->crop_id,
                    'crop_name' => $task->plantingBatch?->crop?->name,
                    'batch_id' => $task->planting_batch_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                ],
            ]));
    }

    public function triggerYieldShortfallAlerts(?int $farmId = null): Collection
    {
        return PlantingBatch::with(['crop', 'farm'])
            ->whereNotNull('planned_quantity')
            ->whereNotNull('actual_quantity')
            ->whereColumn('actual_quantity', '<', 'planned_quantity')
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (PlantingBatch $batch) {
                $gapQuantity = (float) $batch->planned_quantity - (float) $batch->actual_quantity;

                return $this->createOrUpdateAlert([
                    'farm_id' => $batch->farm_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                    'alert_type' => 'yield_shortfall',
                    'severity' => $gapQuantity >= ((float) $batch->planned_quantity * 0.25) ? 'critical' : 'warning',
                    'title' => 'Yield shortfall',
                    'message' => "Batch {$batch->code} is short {$gapQuantity} {$batch->planned_unit} against plan.",
                    'source_type' => PlantingBatch::class,
                    'source_id' => $batch->id,
                    'context' => [
                        'batch_id' => $batch->id,
                        'batch_code' => $batch->code,
                        'crop_id' => $batch->crop_id,
                        'crop_name' => $batch->crop?->name,
                        'planned_quantity' => (float) $batch->planned_quantity,
                        'actual_quantity' => (float) $batch->actual_quantity,
                        'gap_quantity' => round($gapQuantity, 3),
                        'unit' => $batch->planned_unit,
                        'recipient_role' => User::ROLE_FARM_MANAGER,
                    ],
                ]);
            });
    }

    private function createOrUpdateAlert(array $data): Alert
    {
        $payload = $this->buildNotificationPayload($data);

        return Alert::updateOrCreate(
            [
                'alert_type' => $data['alert_type'],
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
                'recipient_role' => $data['recipient_role'] ?? null,
            ],
            [
                ...$data,
                'notification_payload' => $payload,
                'status' => 'unread',
                'read_at' => null,
            ],
        );
    }

    private function buildNotificationPayload(array $data): array
    {
        return [
            'channel' => 'in_app_push_ready',
            'title' => $data['title'],
            'body' => $data['message'],
            'severity' => $data['severity'],
            'type' => $data['alert_type'],
            'farm_id' => $data['farm_id'] ?? null,
            'recipient' => [
                'role' => $data['recipient_role'] ?? null,
                'user_id' => $data['recipient_user_id'] ?? null,
            ],
            'data' => $data['context'] ?? [],
        ];
    }
}
