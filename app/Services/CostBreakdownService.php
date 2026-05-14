<?php

namespace App\Services;

use App\Models\CostBreakdown;
use App\Models\CostRecord;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\ProductionPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CostBreakdownService
{
    /**
     * Calculate cost breakdown for a production plan
     */
    public function calculateForPlan(int $productionPlanId, ?int $userId = null): CostBreakdown
    {
        $plan = ProductionPlan::with('farm')->findOrFail($productionPlanId);

        $costRecords = CostRecord::where('production_plan_id', $productionPlanId)->get();
        $totals = $this->aggregateCosts($costRecords);

        // Get yield from harvest lots
        $totalYield = HarvestLot::whereHas('plantingBatch', fn($q) =>
            $q->where('production_plan_id', $productionPlanId)
        )->sum('raw_quantity');

        // Get revenue from deliveries
        $totalRevenue = $this->getRevenueForPlan($productionPlanId);

        $breakdown = CostBreakdown::updateOrCreate(
            [
                'breakdown_type' => CostBreakdown::BREAKDOWN_TYPES['production_plan'],
                'production_plan_id' => $productionPlanId,
            ],
            [
                'farm_id' => $plan->farm_id,
                'planting_batch_id' => null,
                'period_start' => $plan->created_at->toDateString(),
                'period_end' => now()->toDateString(),
                'period_label' => "Plan: {$plan->code}",
                'total_seed_cost' => $totals['seed'],
                'total_fertilizer_cost' => $totals['fertilizer'],
                'total_chemical_cost' => $totals['chemical_biological'],
                'total_water_cost' => $totals['water'],
                'total_labor_cost' => $totals['labor'],
                'total_machinery_cost' => $totals['machinery'],
                'total_land_rent_cost' => $totals['land_rent'],
                'total_other_cost' => $totals['other'],
                'total_cost' => $totals['total'],
                'total_yield_kg' => $totalYield,
                'cost_per_kg' => $totalYield > 0 ? round($totals['total'] / $totalYield, 4) : null,
                'total_revenue' => $totalRevenue,
                'gross_margin' => $totalRevenue - $totals['total'],
                'gross_margin_percent' => $totalRevenue > 0
                    ? round((($totalRevenue - $totals['total']) / $totalRevenue) * 100, 2)
                    : null,
                'breakdown_by_subcategory' => $this->getSubcategoryBreakdown($costRecords),
                'cost_trends' => $this->getMonthlyCostTrends($costRecords),
                'calculated_by' => $userId,
            ]
        );

        return $breakdown;
    }

    /**
     * Calculate cost breakdown for a planting batch
     */
    public function calculateForBatch(int $plantingBatchId, ?int $userId = null): CostBreakdown
    {
        $batch = PlantingBatch::with('farm')->findOrFail($plantingBatchId);

        $costRecords = CostRecord::where('planting_batch_id', $plantingBatchId)->get();
        $totals = $this->aggregateCosts($costRecords);

        $totalYield = HarvestLot::where('planting_batch_id', $plantingBatchId)->sum('raw_quantity');
        $totalRevenue = $this->getRevenueForBatch($plantingBatchId);

        $breakdown = CostBreakdown::updateOrCreate(
            [
                'breakdown_type' => CostBreakdown::BREAKDOWN_TYPES['planting_batch'],
                'planting_batch_id' => $plantingBatchId,
            ],
            [
                'farm_id' => $batch->farm_id,
                'production_plan_id' => $batch->production_plan_id,
                'period_start' => $batch->planned_start_date ?? $batch->created_at,
                'period_end' => $batch->actual_harvest_date ?? now(),
                'period_label' => "Batch: {$batch->code}",
                'total_seed_cost' => $totals['seed'],
                'total_fertilizer_cost' => $totals['fertilizer'],
                'total_chemical_cost' => $totals['chemical_biological'],
                'total_water_cost' => $totals['water'],
                'total_labor_cost' => $totals['labor'],
                'total_machinery_cost' => $totals['machinery'],
                'total_land_rent_cost' => $totals['land_rent'],
                'total_other_cost' => $totals['other'],
                'total_cost' => $totals['total'],
                'total_yield_kg' => $totalYield,
                'cost_per_kg' => $totalYield > 0 ? round($totals['total'] / $totalYield, 4) : null,
                'total_revenue' => $totalRevenue,
                'gross_margin' => $totalRevenue - $totals['total'],
                'gross_margin_percent' => $totalRevenue > 0
                    ? round((($totalRevenue - $totals['total']) / $totalRevenue) * 100, 2)
                    : null,
                'breakdown_by_subcategory' => $this->getSubcategoryBreakdown($costRecords),
                'cost_trends' => $this->getMonthlyCostTrends($costRecords),
                'calculated_by' => $userId,
            ]
        );

        return $breakdown;
    }

    /**
     * Calculate seasonal cost breakdown for a farm
     */
    public function calculateForFarm(int $farmId, Carbon $start, Carbon $end, ?int $userId = null): CostBreakdown
    {
        $costRecords = CostRecord::where('farm_id', $farmId)
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totals = $this->aggregateCosts($costRecords);

        // Get yield from all batches in this period
        $totalYield = HarvestLot::where('farm_id', $farmId)
            ->whereBetween('harvest_date', [$start->toDateString(), $end->toDateString()])
            ->sum('raw_quantity');

        // Get revenue from deliveries in this period
        $totalRevenue = $this->getRevenueForFarmPeriod($farmId, $start, $end);

        $breakdown = CostBreakdown::updateOrCreate(
            [
                'breakdown_type' => CostBreakdown::BREAKDOWN_TYPES['farm'],
                'farm_id' => $farmId,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
            [
                'production_plan_id' => null,
                'planting_batch_id' => null,
                'period_label' => "Season: {$start->format('M Y')} - {$end->format('M Y')}",
                'total_seed_cost' => $totals['seed'],
                'total_fertilizer_cost' => $totals['fertilizer'],
                'total_chemical_cost' => $totals['chemical_biological'],
                'total_water_cost' => $totals['water'],
                'total_labor_cost' => $totals['labor'],
                'total_machinery_cost' => $totals['machinery'],
                'total_land_rent_cost' => $totals['land_rent'],
                'total_other_cost' => $totals['other'],
                'total_cost' => $totals['total'],
                'total_yield_kg' => $totalYield,
                'cost_per_kg' => $totalYield > 0 ? round($totals['total'] / $totalYield, 4) : null,
                'total_revenue' => $totalRevenue,
                'gross_margin' => $totalRevenue - $totals['total'],
                'gross_margin_percent' => $totalRevenue > 0
                    ? round((($totalRevenue - $totals['total']) / $totalRevenue) * 100, 2)
                    : null,
                'breakdown_by_subcategory' => $this->getSubcategoryBreakdown($costRecords),
                'cost_trends' => $this->getMonthlyCostTrends($costRecords),
                'calculated_by' => $userId,
            ]
        );

        return $breakdown;
    }

    /**
     * Get cost summary for dashboard
     */
    public function getDashboardSummary(int $farmId): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Current month costs
        $currentMonthCosts = CostRecord::where('farm_id', $farmId)
            ->where('occurred_at', '>=', $currentMonth)
            ->get();

        // Last month costs
        $lastMonthCosts = CostRecord::where('farm_id', $farmId)
            ->whereBetween('occurred_at', [$lastMonth->toDateString(), $endOfLastMonth->toDateString()])
            ->get();

        $currentTotals = $this->aggregateCosts($currentMonthCosts);
        $lastTotals = $this->aggregateCosts($lastMonthCosts);

        return [
            'current_month' => [
                'total_cost' => $currentTotals['total'],
                'by_category' => [
                    'seed' => $currentTotals['seed'],
                    'fertilizer' => $currentTotals['fertilizer'],
                    'chemical' => $currentTotals['chemical_biological'],
                    'water' => $currentTotals['water'],
                    'labor' => $currentTotals['labor'],
                    'machinery' => $currentTotals['machinery'],
                    'land_rent' => $currentTotals['land_rent'],
                    'other' => $currentTotals['other'],
                ],
            ],
            'last_month' => [
                'total_cost' => $lastTotals['total'],
                'by_category' => [
                    'seed' => $lastTotals['seed'],
                    'fertilizer' => $lastTotals['fertilizer'],
                    'chemical' => $lastTotals['chemical_biological'],
                    'water' => $lastTotals['water'],
                    'labor' => $lastTotals['labor'],
                    'machinery' => $lastTotals['machinery'],
                    'land_rent' => $lastTotals['land_rent'],
                    'other' => $lastTotals['other'],
                ],
            ],
            'mom_change' => [
                'total_cost' => $lastTotals['total'] > 0
                    ? round((($currentTotals['total'] - $lastTotals['total']) / $lastTotals['total']) * 100, 1)
                    : 0,
            ],
        ];
    }

    // ─── Helper Methods ──────────────────────────────────────────

    private function aggregateCosts(Collection $costRecords): array
    {
        $totals = [
            'seed' => 0,
            'fertilizer' => 0,
            'chemical_biological' => 0,
            'water' => 0,
            'labor' => 0,
            'land_rent' => 0,
            'machinery' => 0,
            'incident' => 0,
            'other' => 0,
            'total' => 0,
        ];

        foreach ($costRecords as $record) {
            $category = $record->cost_category;
            $amount = (float) $record->amount;

            if (isset($totals[$category])) {
                $totals[$category] += $amount;
            } else {
                $totals['other'] += $amount;
            }
            $totals['total'] += $amount;
        }

        return $totals;
    }

    private function getSubcategoryBreakdown(Collection $costRecords): array
    {
        $breakdown = [];

        foreach ($costRecords->groupBy('cost_category') as $category => $records) {
            $breakdown[$category] = [
                'count' => $records->count(),
                'total' => $records->sum('amount'),
                'avg' => $records->count() > 0 ? round($records->avg('amount'), 2) : 0,
            ];
        }

        return $breakdown;
    }

    private function getMonthlyCostTrends(Collection $costRecords): array
    {
        $trends = [];

        foreach ($costRecords->groupBy(fn($r) => Carbon::parse($r->occurred_at)->format('Y-m')) as $month => $records) {
            $trends[$month] = [
                'total' => (float) $records->sum('amount'),
                'by_category' => $records->groupBy('cost_category')
                    ->map(fn($cat) => (float) $cat->sum('amount'))
                    ->toArray(),
            ];
        }

        return $trends;
    }

    private function getRevenueForPlan(int $planId): float
    {
        // Sum revenue from delivery notes for this plan's batches
        $plan = ProductionPlan::find($planId);
        if (!$plan) return 0;

        $batchIds = PlantingBatch::where('production_plan_id', $planId)->pluck('id');

        return \App\Models\DeliveryNote::whereIn('planting_batch_id', $batchIds)
            ->sum('total_amount') ?? 0;
    }

    private function getRevenueForBatch(int $batchId): float
    {
        return \App\Models\DeliveryNote::where('planting_batch_id', $batchId)
            ->sum('total_amount') ?? 0;
    }

    private function getRevenueForFarmPeriod(int $farmId, Carbon $start, Carbon $end): float
    {
        return \App\Models\DeliveryNote::where('farm_id', $farmId)
            ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
            ->sum('total_amount') ?? 0;
    }
}