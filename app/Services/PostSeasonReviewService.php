<?php

namespace App\Services;

use App\Models\PostSeasonReview;
use App\Models\ProductionPlan;
use App\Models\CostRecord;
use App\Models\HarvestLot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostSeasonReviewService
{
    /**
     * Auto-populate from existing data when plan is completed
     */
    public function autoPopulate(int $productionPlanId): PostSeasonReview
    {
        $plan = ProductionPlan::with([
            'plots.beds.harvestLots',
            'costRecords',
            'farmingLogs',
            'incidents',
        ])->findOrFail($productionPlanId);

        $actualTotalCost = CostRecord::where('production_plan_id', $productionPlanId)
            ->sum('total_cost');

        $budgetVariance = $plan->estimated_budget
            ? round((($actualTotalCost - $plan->estimated_budget) / $plan->estimated_budget) * 100, 2)
            : null;

        // Yield analysis from harvest lots
        $totalYield = $harvestLots = $plan->plots
            ->flatMap(fn($p) => $p->beds)
            ->flatMap(fn($b) => $b->harvestLots)
            ->sum('quantity');

        $yieldAnalysis = "Total harvested: {$totalYield} kg across " . count($harvestLots) . " lots";

        // Quality from grade distribution
        $gradeA = HarvestLot::whereHas('bed', fn($q) => $q->whereHas('plot', fn($q2) => $q2->where('production_plan_id', $productionPlanId)))
            ->where('grade', 'A')->sum('quantity');
        $gradeB = HarvestLot::whereHas('bed', fn($q) => $q->whereHas('plot', fn($q2) => $q2->where('production_plan_id', $productionPlanId)))
            ->where('grade', 'B')->sum('quantity');
        $gradeC = HarvestLot::whereHas('bed', fn($q) => $q->whereHas('plot', fn($q2) => $q2->where('production_plan_id', $productionPlanId)))
            ->where('grade', 'C')->sum('quantity');

        $qualityAssessment = "Grade A: {$gradeA}kg, Grade B: {$gradeB}kg, Grade C: {$gradeC}kg";

        // Resource utilization (farming logs count)
        $resourceUtilizationReview = "Total farming logs: " . $plan->farmingLogs->count();
        $resourceUtilizationReview .= ", Total cost records: " . $plan->costRecords->count();

        // Pest/disease from incidents
        $pestIncidents = $plan->incidents->where('incident_type', 'pest_disease')->count();
        $pestDiseaseReview = "Pest/disease incidents: {$pestIncidents}";

        return PostSeasonReview::create([
            'production_plan_id' => $productionPlanId,
            'status' => PostSeasonReview::STATUSES['draft'],
            'actual_total_cost' => $actualTotalCost,
            'budget_variance' => $budgetVariance,
            'yield_analysis' => $yieldAnalysis,
            'quality_assessment' => $qualityAssessment,
            'resource_utilization_review' => $resourceUtilizationReview,
            'pest_disease_review' => $pestDiseaseReview,
        ]);
    }

    public function submit(PostSeasonReview $review, int $userId): PostSeasonReview
    {
        $review->update([
            'status' => PostSeasonReview::STATUSES['submitted'],
            'submitted_by' => $userId,
            'submitted_at' => Carbon::now(),
        ]);

        return $review->fresh();
    }

    public function approve(PostSeasonReview $review, int $userId): PostSeasonReview
    {
        $review->update([
            'status' => PostSeasonReview::STATUSES['approved'],
            'approved_by' => $userId,
            'approved_at' => Carbon::now(),
            'rejected_reason' => null,
        ]);

        return $review->fresh();
    }

    public function reject(PostSeasonReview $review, int $userId, string $reason): PostSeasonReview
    {
        $review->update([
            'status' => PostSeasonReview::STATUSES['rejected'],
            'approved_by' => $userId,
            'approved_at' => Carbon::now(),
            'rejected_reason' => $reason,
        ]);

        return $review->fresh();
    }

    public function getByPlan(int $productionPlanId): ?PostSeasonReview
    {
        return PostSeasonReview::where('production_plan_id', $productionPlanId)->first();
    }
}