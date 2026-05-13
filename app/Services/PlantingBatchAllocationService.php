<?php

namespace App\Services;

use App\Exceptions\AllocationException;
use App\Models\AuditEvent;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;

class PlantingBatchAllocationService
{
    private const DEFAULT_ALLOWED_PLOT_STATUSES = ['available'];

    public function allocate(
        PlantingBatch $batch,
        Plot $plot,
        array $options = [],
        ?int $bedId = null,
        ?int $userId = null
    ): PlantingBatchAllocation {
        $this->validateAllocation($batch, $plot, $options, $bedId);

        return DB::transaction(function () use ($batch, $plot, $options, $bedId, $userId) {
            $allocatedAreaM2 = $options['allocated_area_m2'] ?? $plot->area_m2;
            $notes = $options['notes'] ?? null;

            $allocation = PlantingBatchAllocation::create([
                'planting_batch_id' => $batch->id,
                'plot_id' => $plot->id,
                'bed_id' => $bedId,
                'allocated_area_m2' => $allocatedAreaM2,
                'notes' => $notes,
                'status' => $options['initial_status'] ?? 'allocated',
            ]);

            if ($plot->current_batch_id === null) {
                $plot->update(['current_batch_id' => $batch->id]);
            }

            AuditEvent::recordAllocationCreated(
                farmId: $batch->farm_id,
                allocationId: $allocation->id,
                batchId: $batch->id,
                plotId: $plot->id,
                bedId: $bedId,
                allocatedAreaM2: (float) $allocatedAreaM2,
                userId: $userId,
                actorType: $userId ? 'user' : 'system',
                metadata: $notes ? ['notes' => $notes] : null
            );

            return $allocation;
        });
    }

    public function validateAllocation(
        PlantingBatch $batch,
        Plot $plot,
        array $options = [],
        ?int $bedId = null
    ): void {
        $this->guardFarmMismatch($batch, $plot);
        $this->guardPlotStatus($plot, $options);
        $this->guardAreaValidation($plot, $options);
        $this->guardDuplicateAllocation($batch, $plot, $bedId);
    }

    private function guardFarmMismatch(PlantingBatch $batch, Plot $plot): void
    {
        if ($batch->farm_id !== $plot->farm_id) {
            throw AllocationException::farmMismatch($batch->farm_id, $plot->farm_id);
        }
    }

    private function guardPlotStatus(Plot $plot, array $options): void
    {
        $allowedStatuses = $options['allowed_statuses'] ?? self::DEFAULT_ALLOWED_PLOT_STATUSES;

        if (!in_array($plot->status, $allowedStatuses, true)) {
            throw AllocationException::plotUnavailable($plot->status);
        }
    }

    private function guardAreaValidation(Plot $plot, array $options): void
    {
        $allocatedAreaM2 = $options['allocated_area_m2'] ?? $plot->area_m2;

        if ($allocatedAreaM2 > $plot->area_m2) {
            throw AllocationException::areaExceedsPlot($allocatedAreaM2, (float) $plot->area_m2);
        }
    }

    private function guardDuplicateAllocation(PlantingBatch $batch, Plot $plot, ?int $bedId): void
    {
        $query = PlantingBatchAllocation::where('planting_batch_id', $batch->id)
            ->where('plot_id', $plot->id);

        $query->where(function ($q) use ($bedId) {
            if ($bedId !== null) {
                $q->where('bed_id', $bedId);
            } else {
                $q->whereNull('bed_id');
            }
        });

        if ($query->exists()) {
            throw AllocationException::duplicateAllocation($batch->id, $plot->id, $bedId);
        }
    }
}
