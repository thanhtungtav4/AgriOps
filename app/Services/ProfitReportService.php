<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\HarvestLot;
use App\Models\HarvestModel;
use App\Models\LossProfile;
use App\Models\PlantingBatch;
use App\Models\PriceTable;
use App\Models\ReturnRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProfitReportService
{
    /**
     * Section 25B.2: Estimate revenue for a planting batch
     */
    public function estimateRevenueForBatch(int $plantingBatchId): array
    {
        $batch = PlantingBatch::with(['crop', 'variety', 'farm'])->findOrFail($plantingBatchId);
        $price = $this->getActivePrice($batch->crop_id, $batch->variety_id);

        if (!$price) {
            return [
                'batch_id' => $plantingBatchId,
                'batch_code' => $batch->code,
                'has_pricing' => false,
                'estimated_revenue' => null,
            ];
        }

        $plannedQty = (float) $batch->planned_quantity;
        $gradeRatios = $this->getGradeRatiosFromBatch($batch);

        $gradeAQty = $plannedQty * $gradeRatios['a'];
        $gradeBQty = $plannedQty * $gradeRatios['b'];
        $gradeCQty = $plannedQty * $gradeRatios['c'];
        $rejectQty = $plannedQty * $gradeRatios['reject'];

        $revenueByGrade = [
            'grade_a' => [
                'quantity' => round($gradeAQty, 3),
                'price' => (float) $price->grade_a_price,
                'revenue' => round($gradeAQty * (float) $price->grade_a_price, 2),
            ],
            'grade_b' => [
                'quantity' => round($gradeBQty, 3),
                'price' => (float) $price->grade_b_price,
                'revenue' => round($gradeBQty * (float) $price->grade_b_price, 2),
            ],
            'grade_c' => [
                'quantity' => round($gradeCQty, 3),
                'price', (float) $price->grade_c_price,
                'revenue' => round($gradeCQty * (float) $price->grade_c_price, 2),
            ],
            'reject' => [
                'quantity' => round($rejectQty, 3),
                'side_channel_price' => (float) $price->side_channel_price,
                'revenue' => round($rejectQty * (float) $price->side_channel_price, 2),
            ],
        ];

        $totalRevenue = $revenueByGrade['grade_a']['revenue']
            + $revenueByGrade['grade_b']['revenue']
            + $revenueByGrade['grade_c']['revenue'];

        return [
            'batch_id' => $plantingBatchId,
            'batch_code' => $batch->code,
            'crop_id' => $batch->crop_id,
            'crop_name' => $batch->crop?->name,
            'variety_name' => $batch->variety?->name,
            'has_pricing' => true,
            'planned_quantity' => $plannedQty,
            'unit' => $batch->planned_unit,
            'revenue_by_grade' => $revenueByGrade,
            'total_estimated_revenue' => $totalRevenue,
            'pricing_source' => [
                'grade_a_price' => (float) $price->grade_a_price,
                'grade_b_price' => (float) $price->grade_b_price,
                'grade_c_price' => (float) $price->grade_c_price,
                'side_channel_price' => (float) $price->side_channel_price,
                'effective_from' => $price->effective_from?->toDateString(),
            ],
        ];
    }

    /**
     * Section 25B.3: Actual revenue from deliveries
     * Uses DeliveryNote.net_revenue (already calculated by DeliveryNote API).
     * Falls back to unit price × accepted_quantity if net_revenue not set.
     */
    public function calculateActualRevenueForBatch(int $plantingBatchId): array
    {
        $batch = PlantingBatch::with(['crop', 'variety'])->findOrFail($plantingBatchId);
        $deliveryNotes = DeliveryNote::where('planting_batch_id', $plantingBatchId)->get();

        $totalGross = 0.0;
        $totalReturned = 0.0;
        $sideRevenue = 0.0;
        $breakdown = [];

        foreach ($deliveryNotes as $delivery) {
            // Use net_revenue if available (set by DeliveryNote API).
            // Fall back to unit_price × accepted_quantity.
            $gross = $delivery->net_revenue
                ?? ((float) $delivery->accepted_quantity * (float) ($delivery->unit_price ?? 0));

            $totalGross += $gross;
            $breakdown[] = [
                'delivery_id' => $delivery->id,
                'delivery_code' => $delivery->delivery_code ?? null,
                'delivery_date' => $delivery->delivered_at?->toDateString(),
                'quantity' => (float) $delivery->accepted_quantity,
                'gross_revenue' => round($gross, 2),
            ];

            // Deduct returns
            foreach ($delivery->returnRecords ?? [] as $return) {
                $returnPrice = $this->getPriceAtDate($batch->crop_id, $batch->variety_id, Carbon::parse($return->returned_at);
                $returnPriceVal = $returnPrice ? (float) $returnPrice->grade_a_price : 0;
                $returnVal = (float) $return->returned_quantity * $returnPriceVal;
                $totalReturned += $returnVal;

                if ($return->handling_action === 'resale_secondary') {
                    $sidePrice = $returnPrice ? (float) $returnPrice->side_channel_price : 0;
                    $sideRevenue += (float) $return->returned_quantity * $sidePrice;
                }
            }
        }

        $netRevenue = $totalGross - $totalReturned + $sideRevenue;

        return [
            'batch_id' => $plantingBatchId,
            'batch_code' => $batch->code,
            'total_deliveries' => $deliveryNotes->count(),
            'total_delivered_quantity' => (float) $deliveryNotes->sum('accepted_quantity'),
            'gross_revenue' => round($totalGross, 2),
            'return_deduction' => round($totalReturned, 2),
            'side_channel_revenue' => round($sideRevenue, 2),
            'net_revenue' => round($netRevenue, 2),
            'delivery_breakdown' => $breakdown,
        ];
    }

    /**
     * Section 25B.4: Compare estimated vs actual
     */
    public function compareRevenueForBatch(int $plantingBatchId): array
    {
        $estimated = $this->estimateRevenueForBatch($plantingBatchId);
        $actual = $this->calculateActualRevenueForBatch($plantingBatchId);
        $costBreakdown = app(CostBreakdownService::class)->calculateForBatch($plantingBatchId);

        $estRevenue = $estimated['total_estimated_revenue'] ?? 0.0;
        $actRevenue = $actual['net_revenue'] ?? 0.0;
        $totalCost = (float) $costBreakdown->total_cost;

        $marginVariance = $actRevenue - $estRevenue;

        return [
            'batch_id' => $plantingBatchId,
            'batch_code' => $estimated['batch_code'] ?? $actual['batch_code'],
            'estimated_revenue' => $estRevenue,
            'actual_revenue' => $actRevenue,
            'revenue_variance' => round($marginVariance, 2),
            'revenue_variance_percent' => $estRevenue > 0
                ? round(($marginVariance / $estRevenue * 100, 1)
                : null,
            'total_cost' => $totalCost,
            'estimated_gross_margin' => round($estRevenue - $totalCost, 2),
            'actual_gross_margin' => round($actRevenue - $totalCost, 2),
            'margin_variance' => round($marginVariance, 2),
            'estimated_margin_percent' => $estRevenue > 0
                ? round((($estRevenue - $totalCost) / $estRevenue * 100, 1)
                : null,
            'actual_margin_percent' => $actRevenue > 0
                ? round((($actRevenue - $totalCost) / $actRevenue * 100, 1)
                : null,
            'revenue_by_grade' => $estimated['revenue_by_grade'] ?? null,
            'cost_breakdown' => [
                'seed' => (float) $costBreakdown->total_seed_cost,
                'fertilizer' => (float) $costBreakdown->total_fertilizer_cost,
                'chemical' => (float) $costBreakdown->total_chemical_cost,
                'water' => (float) $costBreakdown->total_water_cost,
                'labor' => (float) $costBreakdown->total_labor_cost,
                'machinery' => (float) $costBreakdown->total_machinery_cost,
                'land_rent' => (float) $costBreakdown->total_land_rent_cost,
                'other' => (float) $costBreakdown->total_other_cost,
                'total' => $totalCost,
            ],
        ];
    }

    /**
     * Section 25B.4: Profit summary by crop/month
     */
    public function profitSummary(int $farmId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now();

        $batches = PlantingBatch::where('farm_id', $farmId)
            ->whereNotNull('actual_start_date')
            ->whereDate('actual_start_date', '>=', $from->toDateString())
            ->whereDate('actual_start_date', '<=', $to->toDateString())
            ->with(['crop', 'variety'])
            ->get();

        $cropSummaries = [];
        $monthSummaries = [];
        $batchProfits = [];

        foreach ($batches as $batch) {
            $comparison = $this->compareRevenueForBatch($batch->id);
            $profit = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->code,
                'crop' => $batch->crop?->name,
                'variety' => $batch->variety?->name,
                'revenue' => $comparison['actual_revenue'] ?? 0.0,
                'cost' => (float) ($comparison['total_cost'] ?? 0),
                'margin' => $comparison['actual_gross_margin'] ?? 0.0,
                'margin_percent' => $comparison['actual_margin_percent'],
            ];

            $batchProfits[] = $profit;

            $cropKey = $batch->crop?->name ?? 'Unknown';
            if (!isset($cropSummaries[$cropKey])) {
                $cropSummaries[$cropKey] = [
                    'crop' => $cropKey,
                    'batches' => 0,
                    'total_revenue' => 0.0,
                    'total_cost' => 0.0,
                    'total_margin' => 0.0,
                ];
            }
            $cropSummaries[$cropKey]['batches']++;
            $cropSummaries[$cropKey]['total_revenue'] += $profit['revenue'];
            $cropSummaries[$cropKey]['total_cost'] += $profit['cost'];
            $cropSummaries[$cropKey]['total_margin'] += $profit['margin'];

            $monthKey = $batch->actual_start_date?->format('Y-m') ?? 'Unknown';
            if (!isset($monthSummaries[$monthKey])) {
                $monthSummaries[$monthKey] = [
                    'month' => $monthKey,
                    'batches' => 0,
                    'total_revenue' => 0.0,
                    'total_cost' => 0.0,
                    'total_margin' => 0.0,
                ];
            }
            $monthSummaries[$monthKey]['batches']++;
            $monthSummaries[$monthKey]['total_revenue'] += $profit['revenue'];
            $monthSummaries[$monthKey]['total_cost'] += $profit['cost'];
            $monthSummaries[$monthKey]['total_margin'] += $profit['margin'];
        }

        foreach ($cropSummaries as &$crop) {
            $crop['margin_percent'] = $crop['total_revenue'] > 0
                ? round($crop['total_margin'] / $crop['total_revenue'] * 100, 1)
                : 0.0;
        }

        usort($batchProfits, fn($a, $b) => $b['margin'] <=> $a['margin']);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'total_batches' => $batches->count(),
            'top_profitable' => array_slice($batchProfits, 0, 5),
            'lowest_profitable' => array_slice(array_reverse($batchProfits), 0, 5),
            'by_crop' => array_values($cropSummaries),
            'by_month' => array_values($monthSummaries),
        ];
    }

    /**
     * Section 25B.4: Efficiency metrics: revenue/m2, yield/m2, cost/m2
     */
    public function efficiencyMetrics(int $farmId): array
    {
        $batches = PlantingBatch::where('farm_id', $farmId)
            ->whereNotNull('actual_area_m2')
            ->with(['crop'])
            ->get();

        $metrics = [];

        foreach ($batches as $batch) {
            $area = (float) $batch->actual_area_m2;
            if ($area <= 0) {
                continue;
            }

            $actual = $this->calculateActualRevenueForBatch($batch->id);
            $cost = (float) \App\Models\CostRecord::where('planting_batch_id', $batch->id)->sum('amount');
            $yieldKg = (float) HarvestLot::where('planting_batch_id', $batch->id)->sum('raw_quantity');

            $metrics[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->code,
                'crop' => $batch->crop?->name,
                'area_m2' => $area,
                'revenue_per_m2' => $area > 0 ? round($actual['net_revenue'] / $area, 2) : 0,
                'yield_per_m2' => $area > 0 ? round($yieldKg / $area, 3) : 0,
                'cost_per_m2' => $area > 0 ? round($cost / $area, 2) : 0,
            ];
        }

        return $metrics;
    }

    // ─── Helpers ─────────────────────────────────────────

    private function getActivePrice(int $cropId, ?int $varietyId = null): ?PriceTable
    {
        $query = PriceTable::query()
            ->where('crop_id', $cropId)
            ->activeForDate(now()->toDateString());

        if ($varietyId) {
            $query->where(fn($q) => $q->where('variety_id', $varietyId)->orWhereNull('variety_id'));
        }

        return $query->orderByDesc('effective_from')->first();
    }

    private function getPriceAtDate(int $cropId, ?int $varietyId, Carbon $date): ?PriceTable
    {
        $query = PriceTable::query()
            ->where('crop_id', $cropId)
            ->activeForDate($date->toDateString());

        if ($varietyId) {
            $query->where(fn($q) => $q->where('variety_id', $varietyId)->orWhereNull('variety_id'));
        }

        return $query->orderByDesc('effective_from')->first();
    }

    /**
     * Load grade ratios from HarvestModel/LossProfile if configured, else use defaults.
     * Issue #7 fix: was always returning hardcoded values.
     */
    private function getGradeRatiosFromBatch(PlantingBatch $batch): array
    {
        $defaults = ['a' => 0.70, 'b' => 0.15, 'c' => 0.10, 'reject' => 0.05];

        // Try HarvestModel attached to the batch's crop
        $harvestModel = HarvestModel::where('crop_id', $batch->crop_id)->first();
        if ($harvestModel) {
            return [
                'a' => (float) ($harvestModel->grade_a_ratio ?? $defaults['a']),
                'b' => (float) ($harvestModel->grade_b_ratio ?? $defaults['b']),
                'c' => (float) ($harvestModel->grade_c_ratio ?? $defaults['c']),
                'reject' => (float) ($harvestModel->reject_ratio ?? $defaults['reject']),
            ];
        }

        // Try LossProfile for this batch
        $lossProfile = LossProfile::where('crop_id', $batch->crop_id)->first();
        if ($lossProfile) {
            return [
                'a' => 1.0
                    - (float) ($lossProfile->rejection_rate ?? $defaults['reject'])
                    - (float) ($lossProfile->processing_loss_rate ?? 0.0),
                'b' => (float) ($lossProfile->grade_b_ratio ?? 0),
                'c' => (float) ($lossProfile->grade_c_ratio ?? 0),
                'reject' => (float) ($lossProfile->rejection_rate ?? $defaults['reject']),
            ];
        }

        return $defaults;
    }
}
