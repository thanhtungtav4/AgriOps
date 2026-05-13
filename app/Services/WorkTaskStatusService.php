<?php

namespace App\Services;

use App\Models\WorkTask;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

class WorkTaskStatusService
{
    private const ALLOWED_TRANSITIONS = [
        'planned' => ['assigned', 'cancelled'],
        'assigned' => ['in_progress', 'cancelled'],
        'in_progress' => ['done', 'cancelled'],
        'done' => [],
        'cancelled' => [],
    ];

    public function transition(WorkTask $task, string $newStatus, ?int $assignedUserId = null, ?string $reason = null, ?string $completionNote = null): WorkTask
    {
        $currentStatus = $task->status;

        if (!$this->isValidTransition($currentStatus, $newStatus)) {
            throw new InvalidArgumentException(
                "Invalid transition from '{$currentStatus}' to '{$newStatus}'."
            );
        }

        $this->validateTransitionRequirements($task, $currentStatus, $newStatus, $assignedUserId, $reason);

        $task->status = $newStatus;

        if ($newStatus === 'assigned' && $assignedUserId) {
            $assignedUser = User::find($assignedUserId);

            if (!$assignedUser) {
                throw new InvalidArgumentException('Assigned user does not exist.');
            }

            if (!$assignedUser->isAdmin() && $assignedUser->farm_id !== $task->farm_id) {
                throw new InvalidArgumentException('Assigned user must belong to the same farm as the task.');
            }

            $task->assigned_user_id = $assignedUserId;
        }

        if ($newStatus === 'in_progress' && !$task->started_at) {
            $task->started_at = Carbon::now();
        }

        if ($newStatus === 'done') {
            $task->completed_at = $task->completed_at ?? Carbon::now();
            if ($completionNote) {
                $task->completion_note = $completionNote;
            }
        }

        if ($newStatus === 'cancelled' && $reason) {
            $task->completion_note = $reason;
        }

        $task->save();

        return $task;
    }

    public function isValidTransition(string $fromStatus, string $toStatus): bool
    {
        return in_array($toStatus, self::ALLOWED_TRANSITIONS[$fromStatus] ?? [], true);
    }

    public function getAllowedTransitions(string $status): array
    {
        return self::ALLOWED_TRANSITIONS[$status] ?? [];
    }

    public function isTerminalStatus(string $status): bool
    {
        return in_array($status, ['done', 'cancelled'], true);
    }

    private function validateTransitionRequirements(
        WorkTask $task,
        string $currentStatus,
        string $newStatus,
        ?int $assignedUserId,
        ?string $reason
    ): void {
        if ($newStatus === 'assigned' && !$assignedUserId) {
            throw new InvalidArgumentException('assigned_user_id is required when transitioning to assigned status.');
        }

        if ($newStatus === 'cancelled' && empty($reason)) {
            throw new InvalidArgumentException('Cancellation reason is required.');
        }
    }
}
