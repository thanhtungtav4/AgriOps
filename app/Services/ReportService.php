<?php

namespace App\Services;

use App\Models\Crop;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\ReturnRecord;
use App\Models\WorkTask;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Section 26.1: Production Report
     * Sản lượng theo ngày/tháng/năm, farm, cây trồng, lứa trồng, loại A/B/C/loại bỏ
     */
    public function productionReport(int $farmId, array $filters = []): array
    {
        $query = HarvestLot::where('farm_id', $farmId)
            ->with(['plantingBatch.crop', 'plot']);

        // Date filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('harvest_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('harvest_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['crop_id'])) {
            $query->whereHas('plantingBatch', fn($q) => $q->where('crop_id', $filters['crop_id']));
        }
        if (!empty($filters['planting_batch_id'])) {
            $query->where('planting_batch_id', $filters['planting_batch_id']);
        }

        $lots = $query->get();

        // Group by dimensions
        $byDate = [];
        $byCrop = [];
        $byBatch = [];
        $byGrade = ['a' => 0, 'b' => 0, 'c' => 0, 'reject' => 0];

        foreach ($lots as $lot) {
            $date = $lot->harvest_date?->format('Y-m-d');
            $cropName = $lot->plantingBatch?->crop?->name ?? 'Unknown';
            $batchCode = $lot->plantingBatch?->code ?? 'Unknown';

            // By date
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['date' => $date, 'total_raw' => 0, 'total_grade_a' => 0, 'total_grade_b' => 0, 'total_grade_c' => 0, 'total_reject' => 0];
            }
            $byDate[$date]['total_raw'] += (float) $lot->raw_quantity;
            $byDate[$date]['total_grade_a'] += (float) $lot->grade_a_quantity;
            $byDate[$date]['total_grade_b'] += (float) $lot->grade_b_quantity;
            $byDate[$date]['total_grade_c'] += (float) $lot->grade_c_quantity;
            $byDate[$date]['total_reject'] += (float) $lot->reject_quantity;

            // By crop
            if (!isset($byCrop[$cropName])) {
                $byCrop[$cropName] = ['crop' => $cropName, 'total_raw' => 0, 'total_grade_a' => 0, 'total_grade_b' => 0, 'total_grade_c' => 0, 'total_reject' => 0];
            }
            $byCrop[$cropName]['total_raw'] += (float) $lot->raw_quantity;
            $byCrop[$cropName]['total_grade_a'] += (float) $lot->grade_a_quantity;
            $byCrop[$cropName]['total_grade_b'] += (float) $lot->grade_b_quantity;
            $byCrop[$cropName]['total_grade_c'] += (float) $lot->grade_c_quantity;
            $byCrop[$cropName]['total_reject'] += (float) $lot->reject_quantity;

            // By batch
            if (!isset($byBatch[$batchCode])) {
                $byBatch[$batchCode] = ['batch_code' => $batchCode, 'total_raw' => 0, 'total_grade_a' => 0, 'total_grade_b' => 0, 'total_grade_c' => 0, 'total_reject' => 0];
            }
            $byBatch[$batchCode]['total_raw'] += (float) $lot->raw_quantity;
            $byBatch[$batchCode]['total_grade_a'] += (float) $lot->grade_a_quantity;
            $byBatch[$batchCode]['total_grade_b'] += (float) $lot->grade_b_quantity;
            $byBatch[$batchCode]['total_grade_c'] += (float) $lot->grade_c_quantity;
            $byBatch[$batchCode]['total_reject'] += (float) $lot->reject_quantity;

            // Totals by grade
            $byGrade['a'] += (float) $lot->grade_a_quantity;
            $byGrade['b'] += (float) $lot->grade_b_quantity;
            $byGrade['c'] += (float) $lot->grade_c_quantity;
            $byGrade['reject'] += (float) $lot->reject_quantity;
        }

        return [
            'summary' => [
                'total_lots' => $lots->count(),
                'total_raw_quantity' => array_sum(array_column($byDate, 'total_raw')),
                'by_grade' => $byGrade,
                'revenue_yield_percent' => round(($byGrade['a'] / max($byGrade['a'] + $byGrade['b'] + $byGrade['c'] + $byGrade['reject'], 1)) * 100, 1),
            ],
            'by_date' => array_values($byDate),
            'by_crop' => array_values($byCrop),
            'by_batch' => array_values($byBatch),
        ];
    }

    /**
     * Section 26.2: Yield Report
     * Năng suất kg/m2, kg/cây, min/avg/max
     */
    public function yieldReport(int $farmId, array $filters = []): array
    {
        $query = PlantingBatch::where('farm_id', $farmId)
            ->with(['crop', 'variety'])
            ->whereNotNull('actual_quantity')
            ->whereNotNull('actual_area_m2');

        if (!empty($filters['crop_id'])) {
            $query->where('crop_id', $filters['crop_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('actual_start_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('actual_start_date', '<=', $filters['to_date']);
        }

        $batches = $query->get();

        $byCrop = [];
        $overallYields = [];

        foreach ($batches as $batch) {
            $area = (float) ($batch->actual_area_m2 ?? 0);
            $yield = (float) ($batch->actual_quantity ?? 0);
            $cropName = $batch->crop?->name ?? 'Unknown';

            if ($area > 0) {
                $yieldPerM2 = round($yield / $area, 3);
                $batchYield = [
                    'batch_id' => $batch->id,
                    'batch_code' => $batch->code,
                    'crop' => $cropName,
                    'variety' => $batch->variety?->name,
                    'area_m2' => $area,
                    'total_yield' => $yield,
                    'yield_per_m2' => $yieldPerM2,
                ];

                $overallYields[] = $yieldPerM2;

                if (!isset($byCrop[$cropName])) {
                    $byCrop[$cropName] = [
                        'crop' => $cropName,
                        'batches' => [],
                        'yields' => [],
                        'min' => PHP_FLOAT_MAX,
                        'max' => 0,
                        'sum' => 0,
                        'count' => 0,
                    ];
                }
                $byCrop[$cropName]['batches'][] = $batchYield;
                $byCrop[$cropName]['yields'][] = $yieldPerM2;
                $byCrop[$cropName]['min'] = min($byCrop[$cropName]['min'], $yieldPerM2);
                $byCrop[$cropName]['max'] = max($byCrop[$cropName]['max'], $yieldPerM2);
                $byCrop[$cropName]['sum'] += $yieldPerM2;
                $byCrop[$cropName]['count']++;
            }
        }

        // Calculate avg for each crop
        foreach ($byCrop as &$crop) {
            $crop['avg_yield_per_m2'] = $crop['count'] > 0 ? round($crop['sum'] / $crop['count'], 3) : 0;
        }

        $allYields = count($overallYields) > 0 ? $overallYields : [0];
        sort($allYields);

        return [
            'summary' => [
                'total_batches' => count($batches),
                'min_yield_per_m2' => min($allYields),
                'max_yield_per_m2' => max($allYields),
                'avg_yield_per_m2' => round(array_sum($allYields) / count($allYields), 3),
            ],
            'by_crop' => array_values($byCrop),
            'all_batch_details' => collect($overallYields)->map(fn($y, $i) => $byCrop[array_keys($byCrop)[$i]]['batches'] ?? [])->flatten(1)->values()->toArray(),
        ];
    }

    /**
     * Section 26.3: Loss Report
     * Hao hụt thu hoạch, sơ chế, đóng gói, lý do
     */
    public function lossReport(int $farmId, array $filters = []): array
    {
        $query = HarvestLot::where('farm_id', $farmId)
            ->with(['plantingBatch.crop']);

        if (!empty($filters['from_date'])) {
            $query->whereDate('harvest_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('harvest_date', '<=', $filters['to_date']);
        }

        $lots = $query->get();

        $harvestLossRatio = [];
        $rejectByReason = [];
        $byCrop = [];

        foreach ($lots as $lot) {
            $raw = (float) $lot->raw_quantity;
            $totalGraded = (float) $lot->grade_a_quantity + (float) $lot->grade_b_quantity + (float) $lot->grade_c_quantity;
            $reject = (float) $lot->reject_quantity;
            $cropName = $lot->plantingBatch?->crop?->name ?? 'Unknown';

            // Harvest loss ratio
            $lossRatio = $raw > 0 ? round(($reject / $raw) * 100, 2) : 0;
            $harvestLossRatio[] = $lossRatio;

            // Reject reasons breakdown
            $reasons = $lot->reject_reasons ?? [];
            foreach ($reasons as $reason => $qty) {
                if (!isset($rejectByReason[$reason])) {
                    $rejectByReason[$reason] = ['reason' => $reason, 'quantity' => 0, 'count' => 0];
                }
                $rejectByReason[$reason]['quantity'] += (float) $qty;
                $rejectByReason[$reason]['count']++;
            }

            // By crop
            if (!isset($byCrop[$cropName])) {
                $byCrop[$cropName] = ['crop' => $cropName, 'total_raw' => 0, 'total_reject' => 0, 'lots' => 0];
            }
            $byCrop[$cropName]['total_raw'] += $raw;
            $byCrop[$cropName]['total_reject'] += $reject;
            $byCrop[$cropName]['lots']++;
        }

        // Calculate avg loss ratio
        $avgLossRatio = count($harvestLossRatio) > 0 ? round(array_sum($harvestLossRatio) / count($harvestLossRatio), 2) : 0;

        foreach ($byCrop as &$crop) {
            $crop['loss_ratio'] = $crop['total_raw'] > 0
                ? round(($crop['total_reject'] / $crop['total_raw']) * 100, 2)
                : 0;
        }

        return [
            'summary' => [
                'total_lots' => count($lots),
                'avg_harvest_loss_ratio' => $avgLossRatio,
                'min_loss_ratio' => min($harvestLossRatio ?: [0]),
                'max_loss_ratio' => max($harvestLossRatio ?: [0]),
            ],
            'by_reject_reason' => array_values($rejectByReason),
            'by_crop' => array_values($byCrop),
        ];
    }

    /**
     * Section 26.5: Quality & Return Report
     * Tỷ lệ trả, lý do trả, farm/lô có tỷ lệ trả cao
     */
    public function qualityReturnReport(int $farmId, array $filters = []): array
    {
        $query = ReturnRecord::where('farm_id', $farmId)
            ->with(['deliveryNote.packingLot']);

        if (!empty($filters['from_date'])) {
            $query->whereDate('returned_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('returned_at', '<=', $filters['to_date']);
        }

        $returns = $query->get();

        // Delivery acceptance rate
        $totalDeliveredQty = $returns->sum('accepted_quantity');
        $totalReturnedQty = $returns->sum('returned_quantity');

        // By reason
        $byReason = [];
        // By handling action
        $byAction = [];
        $byCrop = [];

        foreach ($returns as $return) {
            $reason = $return->return_reason ?? 'unknown';
            $action = $return->handling_action ?? 'unknown';
            $qty = (float) $return->returned_quantity;
            $cropName = $return->deliveryNote?->packingLot?->crop?->name
                ?? $return->deliveryNote?->plantingBatch?->crop?->name
                ?? 'Unknown';

            if (!isset($byReason[$reason])) {
                $byReason[$reason] = ['reason' => $reason, 'quantity' => 0, 'count' => 0];
            }
            $byReason[$reason]['quantity'] += $qty;
            $byReason[$reason]['count']++;

            if (!isset($byAction[$action])) {
                $byAction[$action] = ['action' => $action, 'quantity' => 0, 'count' => 0];
            }
            $byAction[$action]['quantity'] += $qty;
            $byAction[$action]['count']++;

            if (!isset($byCrop[$cropName])) {
                $byCrop[$cropName] = ['crop' => $cropName, 'total_returned' => 0, 'count' => 0];
            }
            $byCrop[$cropName]['total_returned'] += $qty;
            $byCrop[$cropName]['count']++;
        }

        // Calculate return rate
        $returnRate = $totalDeliveredQty > 0
            ? round(($totalReturnedQty / $totalDeliveredQty) * 100, 2)
            : 0;

        return [
            'summary' => [
                'total_returns' => $returns->count(),
                'total_returned_quantity' => $totalReturnedQty,
                'total_delivered_quantity' => $totalDeliveredQty,
                'return_rate_percent' => $returnRate,
            ],
            'by_return_reason' => array_values($byReason),
            'by_handling_action' => array_values($byAction),
            'by_crop' => array_values($byCrop),
        ];
    }

    /**
     * Section 26.1: Work task completion report
     */
    public function taskCompletionReport(int $farmId, array $filters = []): array
    {
        $query = WorkTask::where('farm_id', $farmId);

        if (!empty($filters['from_date'])) {
            $query->whereDate('planned_due_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('planned_due_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['planting_batch_id'])) {
            $query->where('planting_batch_id', $filters['planting_batch_id']);
        }

        $tasks = $query->get();

        $byStatus = [];
        $overdueCount = 0;
        $totalDaysLate = 0;

        foreach ($tasks as $task) {
            $status = $task->status ?? 'unknown';
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = ['status' => $status, 'count' => 0];
            }
            $byStatus[$status]['count']++;

            if (in_array($status, ['completed', 'reported']) && $task->planned_due_date) {
                $dueDate = Carbon::parse($task->planned_due_date);
                $completedDate = $task->actual_completed_at
                    ? Carbon::parse($task->actual_completed_at)
                    : now();

                if ($completedDate->gt($dueDate)) {
                    $overdueCount++;
                    $totalDaysLate += $dueDate->diffInDays($completedDate);
                }
            }
        }

        return [
            'summary' => [
                'total_tasks' => $tasks->count(),
                'overdue_count' => $overdueCount,
                'on_time_rate_percent' => $tasks->count() > 0
                    ? round((($tasks->count() - $overdueCount) / $tasks->count()) * 100, 1)
                    : 0,
                'avg_days_late' => $overdueCount > 0 ? round($totalDaysLate / $overdueCount, 1) : 0,
            ],
            'by_status' => array_values($byStatus),
        ];
    }
}