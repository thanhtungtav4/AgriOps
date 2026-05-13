<?php

namespace App\Services;

use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Models\User;
use InvalidArgumentException;

class HarvestLotService
{
    public function __construct(
        private readonly HarvestEligibilityService $eligibilityService
    ) {}

    public function create(PlantingBatch $batch, User $user, array $data): HarvestLot
    {
        $this->eligibilityService->assertCanHarvest($batch, $data['harvest_date']);
        $this->assertGradeBreakdownIsValid($data);

        $inspection = PreHarvestInspection::where('planting_batch_id', $batch->id)
            ->where('status', 'approved')
            ->orderByDesc('inspected_at')
            ->orderByDesc('id')
            ->first();

        $lot = HarvestLot::create([
            'farm_id' => $batch->farm_id,
            'planting_batch_id' => $batch->id,
            'pre_harvest_inspection_id' => $inspection?->id,
            'work_task_id' => $data['work_task_id'] ?? null,
            'plot_id' => $data['plot_id'] ?? null,
            'bed_id' => $data['bed_id'] ?? null,
            'harvested_by_user_id' => $user->id,
            'code' => $data['code'] ?? null,
            'harvest_date' => $data['harvest_date'],
            'raw_quantity' => $data['raw_quantity'],
            'unit' => $data['unit'] ?? 'kg',
            'grade_a_quantity' => $data['grade_a_quantity'] ?? 0,
            'grade_b_quantity' => $data['grade_b_quantity'] ?? 0,
            'grade_c_quantity' => $data['grade_c_quantity'] ?? 0,
            'reject_quantity' => $data['reject_quantity'] ?? 0,
            'reject_reasons' => $data['reject_reasons'] ?? [],
            'notes' => $data['notes'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        $batch->update([
            'actual_harvest_date' => $batch->actual_harvest_date ?? $lot->harvest_date,
            'actual_quantity' => HarvestLot::where('planting_batch_id', $batch->id)->sum('raw_quantity'),
            'status' => $batch->status === 'completed' ? $batch->status : 'harvesting',
        ]);

        return $lot;
    }

    private function assertGradeBreakdownIsValid(array $data): void
    {
        $rawQuantity = (float) $data['raw_quantity'];

        if ($rawQuantity <= 0) {
            throw new InvalidArgumentException('raw_quantity must be greater than zero.');
        }

        $gradeTotal = (float) ($data['grade_a_quantity'] ?? 0)
            + (float) ($data['grade_b_quantity'] ?? 0)
            + (float) ($data['grade_c_quantity'] ?? 0)
            + (float) ($data['reject_quantity'] ?? 0);

        if ($gradeTotal > $rawQuantity) {
            throw new InvalidArgumentException('Grade breakdown total cannot exceed raw_quantity.');
        }
    }
}
