<?php

namespace App\Services;

use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Models\User;
use InvalidArgumentException;

class PreHarvestInspectionService
{
    public function createInspection(PlantingBatch $batch, User $user, array $data): PreHarvestInspection
    {
        $checklist = $data['checklist'];
        $hasFailedCriteria = $this->hasFailedCriteria($checklist);

        $status = 'submitted';
        $approvedByUserId = null;
        $approvedAt = null;
        $rejectedAt = null;
        $rejectionReason = null;

        if ($hasFailedCriteria) {
            $status = 'rejected';
            $rejectedAt = now();
            $rejectionReason = $data['rejection_reason'] ?? 'One or more inspection criteria failed.';
        } elseif ($user->canApprove()) {
            $status = 'approved';
            $approvedByUserId = $user->id;
            $approvedAt = now();
        }

        return PreHarvestInspection::create([
            'farm_id' => $batch->farm_id,
            'planting_batch_id' => $batch->id,
            'plot_id' => $data['plot_id'] ?? null,
            'inspector_user_id' => $user->id,
            'approved_by_user_id' => $approvedByUserId,
            'status' => $status,
            'inspected_at' => $data['inspected_at'] ?? now(),
            'approved_at' => $approvedAt,
            'rejected_at' => $rejectedAt,
            'checklist' => $checklist,
            'notes' => $data['notes'] ?? null,
            'rejection_reason' => $rejectionReason,
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    public function approve(PreHarvestInspection $inspection, User $approver): PreHarvestInspection
    {
        if (!$approver->canApprove()) {
            throw new InvalidArgumentException('Only approver roles can approve pre-harvest inspections.');
        }

        if ($inspection->farm_id !== $approver->farm_id && !$approver->isAdmin()) {
            throw new InvalidArgumentException('Approver does not belong to the inspection farm.');
        }

        if ($inspection->hasFailedCriteria()) {
            throw new InvalidArgumentException('Cannot approve inspection with failed criteria.');
        }

        $inspection->update([
            'status' => 'approved',
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return $inspection->refresh();
    }

    public function reject(PreHarvestInspection $inspection, User $approver, string $reason): PreHarvestInspection
    {
        if (!$approver->canApprove()) {
            throw new InvalidArgumentException('Only approver roles can reject pre-harvest inspections.');
        }

        if ($inspection->farm_id !== $approver->farm_id && !$approver->isAdmin()) {
            throw new InvalidArgumentException('Approver does not belong to the inspection farm.');
        }

        $inspection->update([
            'status' => 'rejected',
            'approved_by_user_id' => $approver->id,
            'approved_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $inspection->refresh();
    }

    private function hasFailedCriteria(array $checklist): bool
    {
        foreach ($checklist as $item) {
            if (($item['passed'] ?? false) !== true) {
                return true;
            }
        }

        return false;
    }
}
