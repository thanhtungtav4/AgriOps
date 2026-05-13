<?php

namespace App\Services;

use App\Models\CostRecord;
use App\Models\DeliveryNote;
use App\Models\ProductionPlan;

class CostingService
{
    public function createCostRecord(array $data): CostRecord
    {
        return CostRecord::create($data);
    }

    public function applyEstimatedMargin(ProductionPlan $plan, ?array $pricingSnapshot = null): ProductionPlan
    {
        $estimatedCost = (float) ($plan->estimated_cost ?? 0);
        $estimatedRevenue = (float) ($plan->estimated_revenue ?? 0);
        $estimatedMargin = $estimatedRevenue - $estimatedCost;
        $marginPercent = $estimatedRevenue > 0 ? ($estimatedMargin / $estimatedRevenue) * 100 : 0;

        $plan->update([
            'estimated_margin' => round($estimatedMargin, 2),
            'margin_percent' => round($marginPercent, 2),
            'pricing_snapshot' => $pricingSnapshot,
        ]);

        return $plan->refresh();
    }

    public function dashboard(?int $farmId = null): array
    {
        $plans = ProductionPlan::query()
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId));

        $costs = CostRecord::query()
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId));

        $deliveries = DeliveryNote::query()
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId));

        $estimatedRevenue = (float) (clone $plans)->sum('estimated_revenue');
        $estimatedCost = (float) (clone $plans)->sum('estimated_cost');
        $actualCost = (float) $costs->sum('amount');
        $actualRevenue = (float) $deliveries->sum('net_revenue');

        return [
            'estimated_revenue' => round($estimatedRevenue, 2),
            'estimated_cost' => round($estimatedCost, 2),
            'estimated_margin' => round($estimatedRevenue - $estimatedCost, 2),
            'actual_revenue' => round($actualRevenue, 2),
            'actual_cost' => round($actualCost, 2),
            'actual_margin' => round($actualRevenue - $actualCost, 2),
            'plan_count' => (clone $plans)->count(),
            'cost_record_count' => CostRecord::query()
                ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
                ->count(),
        ];
    }
}
