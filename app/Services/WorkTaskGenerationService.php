<?php

namespace App\Services;

use App\Models\GrowthStage;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\WorkTask;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class WorkTaskGenerationService
{
    public function generateFromBatch(
        PlantingBatch $batch,
        ?int $allocationId = null
    ): array {
        $growthStages = $this->getGrowthStagesForBatch($batch);

        if ($growthStages->isEmpty()) {
            return [
                'tasks' => new Collection(),
                'created_count' => 0,
                'existing_count' => 0,
                'skipped_count' => 0,
            ];
        }

        $allocation = $allocationId
            ? PlantingBatchAllocation::where('planting_batch_id', $batch->id)->find($allocationId)
            : null;

        if ($allocationId && !$allocation) {
            throw new InvalidArgumentException('Allocation does not belong to this planting batch.');
        }

        $tasks = new Collection();
        $createdCount = 0;
        $existingCount = 0;
        $skippedCount = 0;

        $batchStartDate = $batch->planned_start_date
            ?? Carbon::today();

        $cumulativeDays = 0;

        foreach ($growthStages->sortBy('order') as $stage) {
            $plannedStart = $batchStartDate->copy()->addDays($cumulativeDays);
            $plannedDue = $plannedStart->copy()->addDays($stage->duration_days - 1);

            $existingTask = $this->findExistingTask($batch, $stage, $allocation);

            if ($existingTask) {
                $existingCount++;
                $tasks->push($existingTask);
                $skippedCount++;
                $cumulativeDays += $stage->duration_days;
                continue;
            }

            $task = WorkTask::create([
                'farm_id' => $batch->farm_id,
                'planting_batch_id' => $batch->id,
                'planting_batch_allocation_id' => $allocation?->id,
                'plot_id' => $allocation?->plot_id,
                'bed_id' => $allocation?->bed_id,
                'growth_stage_id' => $stage->id,
                'title' => $this->deriveTitle($stage),
                'task_type' => $this->deriveTaskType($stage),
                'status' => 'planned',
                'priority' => 'normal',
                'planned_start_date' => $plannedStart,
                'planned_due_date' => $plannedDue,
                'metadata' => [
                    'growth_stage_order' => $stage->order,
                    'stage_duration_days' => $stage->duration_days,
                    'generated_from' => 'WorkTaskGenerationService',
                ],
            ]);

            $createdCount++;
            $tasks->push($task);
            $cumulativeDays += $stage->duration_days;
        }

        return [
            'tasks' => $tasks,
            'created_count' => $createdCount,
            'existing_count' => $existingCount,
            'skipped_count' => $skippedCount,
        ];
    }

    protected function getGrowthStagesForBatch(PlantingBatch $batch): Collection
    {
        $query = GrowthStage::where('crop_id', $batch->crop_id);

        if ($batch->variety_id) {
            $query->where(function ($q) use ($batch) {
                $q->where('variety_id', $batch->variety_id)
                  ->orWhereNull('variety_id');
            });
        } else {
            $query->whereNull('variety_id');
        }

        return $query->orderBy('order')->get();
    }

    protected function findExistingTask(
        PlantingBatch $batch,
        GrowthStage $stage,
        ?PlantingBatchAllocation $allocation
    ): ?WorkTask {
        $query = WorkTask::where('planting_batch_id', $batch->id)
            ->where('growth_stage_id', $stage->id);

        if ($allocation) {
            $query->where('planting_batch_allocation_id', $allocation->id);
        } else {
            $query->whereNull('planting_batch_allocation_id');
        }

        return $query->first();
    }

    protected function deriveTitle(GrowthStage $stage): string
    {
        $name = $stage->name ?? '';
        $code = $stage->code ?? '';

        if (!empty($code)) {
            $codeNormalized = strtolower(trim($code));
            $nameNormalized = strtolower(trim($name));
            $codePartInName = str_contains($nameNormalized, $codeNormalized);
            $namePartInCode = str_contains($codeNormalized, $nameNormalized) && strlen($codeNormalized) > 3;

            if (!$codePartInName && !$namePartInCode) {
                return ucfirst($codeNormalized) . ' - ' . $name;
            }
        }

        return $name;
    }

    protected function deriveTaskType(GrowthStage $stage): string
    {
        $code = strtolower($stage->code ?? '');
        $name = strtolower($stage->name ?? '');

        $typeMap = [
            'soil_prep' => 'soil_prep',
            'soil prep' => 'soil_prep',
            'preparation' => 'soil_prep',
            'planting' => 'planting',
            'gieo' => 'planting',
            'trồng' => 'planting',
            'seedling' => 'nursery',
            'cây con' => 'nursery',
            'nursery' => 'nursery',
            'growing' => 'growing',
            'sinh trưởng' => 'growing',
            'growth' => 'growing',
            'flowering' => 'flowering',
            'ra hoa' => 'flowering',
            'flower' => 'flowering',
            'fruiting' => 'fruiting',
            'nuôi trái' => 'fruiting',
            'fruit' => 'fruiting',
            'harvest' => 'harvest',
            'thu hoạch' => 'harvest',
            'harvesting' => 'harvest',
            'soil reform' => 'soil_reform',
            'reform' => 'soil_reform',
            'cai tao' => 'soil_reform',
        ];

        foreach ($typeMap as $key => $type) {
            if (str_contains($code, $key) || str_contains($name, $key)) {
                return $type;
            }
        }

        return 'general';
    }
}
