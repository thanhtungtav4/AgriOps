<?php

namespace App\Services;

use App\Models\PlantingBatch;
use InvalidArgumentException;

class PlantingBatchLifecycleService
{
    private const STATUSES = [
        'planned',
        'planned_kh',
        'approved',
        'soil_prep',
        'planting',
        'growing',
        'flowering',
        'fruiting',
        'harvesting',
        'completed',
        'cancelled',
    ];

    private const TERMINAL_STATUSES = ['completed', 'cancelled'];

    private const ACTIVE_STATUSES = [
        'planned',
        'planned_kh',
        'approved',
        'soil_prep',
        'planting',
        'growing',
        'flowering',
        'fruiting',
        'harvesting',
    ];

    private const ALLOWED_TRANSITIONS = [
        'planned' => ['approved', 'cancelled'],
        'planned_kh' => ['approved', 'cancelled'],
        'approved' => ['soil_prep', 'cancelled'],
        'soil_prep' => ['planting', 'cancelled'],
        'planting' => ['growing', 'cancelled'],
        'growing' => ['flowering', 'fruiting', 'cancelled'],
        'flowering' => ['fruiting', 'cancelled'],
        'fruiting' => ['harvesting', 'cancelled'],
        'harvesting' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    private const CANCELLABLE_STATUSES = [
        'planned',
        'planned_kh',
        'approved',
        'soil_prep',
        'planting',
        'growing',
        'flowering',
        'fruiting',
        'harvesting',
    ];

    public function getAllowedStatuses(): array
    {
        return self::STATUSES;
    }

    public function getTerminalStatuses(): array
    {
        return self::TERMINAL_STATUSES;
    }

    public function isTerminal(string $status): bool
    {
        return in_array($status, self::TERMINAL_STATUSES, true);
    }

    public function canTransition(PlantingBatch $batch, string $toStatus): bool
    {
        $fromStatus = $batch->status;

        if (!isset(self::ALLOWED_TRANSITIONS[$fromStatus])) {
            return false;
        }

        if (!in_array($toStatus, self::STATUSES, true)) {
            return false;
        }

        return in_array($toStatus, self::ALLOWED_TRANSITIONS[$fromStatus], true);
    }

    public function validateTransition(PlantingBatch $batch, string $toStatus, ?string $reason = null): void
    {
        $fromStatus = $batch->status;

        if ($this->isTerminal($fromStatus)) {
            throw new InvalidArgumentException(
                "Cannot transition from terminal status '{$fromStatus}'.",
                422
            );
        }

        if (!in_array($toStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException(
                "Invalid status '{$toStatus}'. Allowed statuses: " . implode(', ', self::STATUSES),
                422
            );
        }

        if (!$this->canTransition($batch, $toStatus)) {
            throw new InvalidArgumentException(
                "Invalid transition from '{$fromStatus}' to '{$toStatus}'.",
                422
            );
        }

        if ($toStatus === 'cancelled') {
            $this->validateCancellationReason($reason);
        }
    }

    public function validateCancellationReason(?string $reason): void
    {
        if (empty($reason)) {
            throw new InvalidArgumentException(
                'Cancellation reason is required.',
                422
            );
        }

        if (strlen(trim($reason)) < 10) {
            throw new InvalidArgumentException(
                'Cancellation reason must be at least 10 characters.',
                422
            );
        }
    }

    public function transition(PlantingBatch $batch, string $toStatus, ?string $reason = null): PlantingBatch
    {
        $this->validateTransition($batch, $toStatus, $reason);

        $updateData = ['status' => $toStatus];

        if ($toStatus === 'cancelled' && $reason !== null) {
            $metadata = $batch->metadata ?? [];
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = now()->toIso8601String();
            $updateData['metadata'] = $metadata;
        }

        if ($toStatus === 'planting' && $batch->actual_start_date === null) {
            $updateData['actual_start_date'] = now()->toDateString();
        }

        if ($toStatus === 'completed' && $batch->actual_harvest_date === null) {
            $updateData['actual_harvest_date'] = now()->toDateString();
        }

        $batch->update($updateData);
        $batch->refresh();

        return $batch;
    }

    public function isCancellable(string $status): bool
    {
        return in_array($status, self::CANCELLABLE_STATUSES, true);
    }
}
