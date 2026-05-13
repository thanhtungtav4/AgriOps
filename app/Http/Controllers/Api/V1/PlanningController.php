<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ProductionPlan;
use App\Services\CostingService;
use App\Services\PlanningService;
use App\Services\PriceTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PlanningService $planningService,
        private PriceTableService $priceTableService,
        private CostingService $costingService
    ) {}

    public function calculate(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string',
            'frequency' => 'required|string',
            'crop_id' => 'required|exists:crops,id',
            'variety_id' => 'nullable|exists:crop_varieties,id',
            'farm_id' => 'nullable|exists:farms,id',
            'target_date' => 'required|date',
        ]);

        if (!$user->isAdmin()) {
            if (isset($validated['farm_id']) && (int) $validated['farm_id'] !== (int) $user->farm_id) {
                return $this->forbiddenError(
                    'You do not have permission to calculate plans for this farm.',
                    [
                        'required_role' => 'admin or own farm',
                        'current_role' => $user->role,
                    ]
                );
            }

            $validated['farm_id'] ??= $user->farm_id;
        }

        try {
            $result = $this->planningService->calculate($validated);

            return response()->json($result);
        } catch (DomainException $e) {
            $details = [];
            if ($e->field) {
                $details['field'] = $e->field;
            }
            if (!empty($e->context)) {
                $details = array_merge($details, $e->context);
            }
            return $this->error('PLANNING_ERROR', $e->getMessage(), $details, 422);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('User must be assigned to a farm to create production plans.');
        }

        $validated = $request->validate([
            'crop_id' => 'required|exists:crops,id',
            'variety_id' => 'nullable|exists:crop_varieties,id',
            'farm_id' => 'required|exists:farms,id',
            'supply_contract_id' => 'nullable|exists:supply_contracts,id',
            'supply_demand_id' => 'nullable|exists:supply_demands,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string',
            'target_delivery_date' => 'required|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_revenue' => 'nullable|numeric|min:0',
            'margin_percent' => 'nullable|numeric|min:-100|max:100',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (!$user->isAdmin() && $validated['farm_id'] !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to create production plans for this farm.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        try {
            $validated['estimated_cost'] = $validated['estimated_cost'] ?? 0;

            $pricingSnapshot = null;
            if (!isset($validated['estimated_revenue'])) {
                $price = $this->priceTableService->findActivePrice(
                    (int) $validated['crop_id'],
                    $validated['variety_id'] ?? null,
                    $validated['unit'],
                    $validated['target_delivery_date'],
                    (int) $validated['farm_id'],
                );

                if ($price) {
                    $validated['estimated_revenue'] = round((float) $validated['quantity'] * (float) $price->grade_a_price, 2);
                    $pricingSnapshot = $this->priceTableService->snapshot($price);
                }
            }

            $validated['estimated_revenue'] = $validated['estimated_revenue'] ?? 0;
            $plan = ProductionPlan::create($validated);
            $plan = $this->costingService->applyEstimatedMargin($plan, $pricingSnapshot);

            return $this->success($plan->load(['crop', 'variety', 'farm', 'supplyContract']), 201);
        } catch (DomainException $e) {
            return $this->error('DOMAIN_ERROR', $e->getMessage(), [], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ProductionPlan::with(['crop', 'variety', 'farm', 'supplyContract']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        } else {
            if ($request->has('crop_id')) {
                $query->where('crop_id', $request->crop_id);
            }

            if ($request->has('farm_id')) {
                $query->where('farm_id', $request->farm_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $plans = $query->orderBy('target_delivery_date', 'desc')->get();

        return $this->success($plans);
    }
}
