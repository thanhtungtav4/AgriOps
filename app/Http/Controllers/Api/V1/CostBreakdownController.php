<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\CostBreakdown;
use App\Services\CostBreakdownService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostBreakdownController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CostBreakdownService $costBreakdownService
    ) {}

    /**
     * List cost breakdowns
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CostBreakdown::with(['farm', 'productionPlan', 'plantingBatch']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->filled('breakdown_type')) {
            $query->where('breakdown_type', $request->input('breakdown_type'));
        }

        if ($request->filled('production_plan_id')) {
            $query->where('production_plan_id', $request->input('production_plan_id'));
        }

        if ($request->filled('planting_batch_id')) {
            $query->where('planting_batch_id', $request->input('planting_batch_id'));
        }

        $breakdowns = $query->orderByDesc('period_end')->paginate($request->input('per_page', 20));

        return $this->success($breakdowns);
    }

    /**
     * Get cost breakdown by ID
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $breakdown = CostBreakdown::with([
            'farm',
            'productionPlan',
            'plantingBatch',
            'calculatedBy',
        ])->findOrFail($id);

        $user = $request->user();

        if (!$user->isAdmin() && $breakdown->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to view this breakdown.');
        }

        // Add calculated percentages
        $breakdown->cost_percentages = $breakdown->getCostPercentages();

        return $this->success($breakdown);
    }

    /**
     * Calculate breakdown for a production plan
     */
    public function calculateForPlan(Request $request, int $productionPlanId): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to calculate breakdowns.');
        }

        // Verify that production plan belongs to user's farm
        $productionPlan = \App\Models\ProductionPlan::find($productionPlanId);
        if (!$user->isAdmin() && $productionPlan && $productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to calculate breakdowns for another farm\'s production plan.');
        }

        $breakdown = $this->costBreakdownService->calculateForPlan($productionPlanId, $user->id);

        $breakdown->cost_percentages = $breakdown->getCostPercentages();

        return $this->success(
            $breakdown->load(['farm', 'productionPlan']),
            201,
            'Cost breakdown calculated'
        );
    }

    /**
     * Calculate breakdown for a planting batch
     */
    public function calculateForBatch(Request $request, int $plantingBatchId): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to calculate breakdowns.');
        }

        // Verify that planting batch belongs to user's farm
        $plantingBatch = \App\Models\PlantingBatch::find($plantingBatchId);
        if (!$user->isAdmin() && $plantingBatch && $plantingBatch->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to calculate breakdowns for another farm\'s planting batch.');
        }

        $breakdown = $this->costBreakdownService->calculateForBatch($plantingBatchId, $user->id);

        $breakdown->cost_percentages = $breakdown->getCostPercentages();

        return $this->success(
            $breakdown->load(['farm', 'plantingBatch']),
            201,
            'Cost breakdown calculated'
        );
    }

    /**
     * Calculate seasonal breakdown for a farm
     */
    public function calculateForFarm(Request $request, int $farmId): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && $user->farm_id !== $farmId) {
            return $this->forbiddenError('You do not have permission to access this farm.');
        }

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to calculate breakdowns.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $breakdown = $this->costBreakdownService->calculateForFarm(
            $farmId,
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            $user->id
        );

        $breakdown->cost_percentages = $breakdown->getCostPercentages();

        return $this->success(
            $breakdown->load(['farm']),
            201,
            'Seasonal cost breakdown calculated'
        );
    }

    /**
     * Get dashboard summary (current vs last month)
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('You are not assigned to a farm.');
        }

        $farmId = $user->isAdmin()
            ? ($request->input('farm_id') ?? $user->farm_id)
            : $user->farm_id;

        $summary = $this->costBreakdownService->getDashboardSummary($farmId);

        return $this->success($summary);
    }

    /**
     * Compare costs between two periods
     */
    public function compare(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('You are not assigned to a farm.');
        }

        $validated = $request->validate([
            'farm_id' => 'nullable|exists:farms,id',
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
        ]);

        $farmId = $user->isAdmin()
            ? ($validated['farm_id'] ?? $user->farm_id)
            : $user->farm_id;

        // Calculate both periods
        $period1 = $this->costBreakdownService->calculateForFarm(
            $farmId,
            Carbon::parse($validated['period1_start']),
            Carbon::parse($validated['period1_end'])
        );

        $period2 = $this->costBreakdownService->calculateForFarm(
            $farmId,
            Carbon::parse($validated['period2_start']),
            Carbon::parse($validated['period2_end'])
        );

        $compare = [
            'period1' => [
                'start' => $validated['period1_start'],
                'end' => $validated['period1_end'],
                'total_cost' => (float) $period1->total_cost,
                'total_yield' => (float) $period1->total_yield_kg,
                'cost_per_kg' => (float) $period1->cost_per_kg,
                'cost_by_category' => [
                    'seed' => (float) $period1->total_seed_cost,
                    'fertilizer' => (float) $period1->total_fertilizer_cost,
                    'chemical' => (float) $period1->total_chemical_cost,
                    'water' => (float) $period1->total_water_cost,
                    'labor' => (float) $period1->total_labor_cost,
                    'machinery' => (float) $period1->total_machinery_cost,
                    'land_rent' => (float) $period1->total_land_rent_cost,
                    'other' => (float) $period1->total_other_cost,
                ],
            ],
            'period2' => [
                'start' => $validated['period2_start'],
                'end' => $validated['period2_end'],
                'total_cost' => (float) $period2->total_cost,
                'total_yield' => (float) $period2->total_yield_kg,
                'cost_per_kg' => (float) $period2->cost_per_kg,
                'cost_by_category' => [
                    'seed' => (float) $period2->total_seed_cost,
                    'fertilizer' => (float) $period2->total_fertilizer_cost,
                    'chemical' => (float) $period2->total_chemical_cost,
                    'water' => (float) $period2->total_water_cost,
                    'labor' => (float) $period2->total_labor_cost,
                    'machinery' => (float) $period2->total_machinery_cost,
                    'land_rent' => (float) $period2->total_land_rent_cost,
                    'other' => (float) $period2->total_other_cost,
                ],
            ],
        ];

        // Calculate variance
        $compare['variance'] = [
            'total_cost_change' => $period1->total_cost > 0
                ? round((($period2->total_cost - $period1->total_cost) / $period1->total_cost) * 100, 1)
                : 0,
            'cost_per_kg_change' => $period1->cost_per_kg > 0
                ? round((($period2->cost_per_kg - $period1->cost_per_kg) / $period1->cost_per_kg) * 100, 1)
                : 0,
        ];

        return $this->success($compare);
    }

    /**
     * Delete a breakdown
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $breakdown = CostBreakdown::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete breakdowns.');
        }

        $breakdown->delete();

        return $this->success(null, 200, 'Cost breakdown deleted');
    }
}