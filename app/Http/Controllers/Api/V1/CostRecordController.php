<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\CostRecord;
use App\Services\CostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CostRecordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CostingService $costingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = CostRecord::with(['farm', 'productionPlan', 'plantingBatch', 'recordedBy']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('cost_category')) {
            $query->where('cost_category', $request->string('cost_category'));
        }

        return $this->success($query->orderByDesc('occurred_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'production_plan_id' => ['nullable', 'exists:production_plans,id'],
            'planting_batch_id' => ['nullable', 'exists:planting_batches,id'],
            'cost_category' => ['required', Rule::in(CostRecord::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'occurred_at' => ['required', 'date'],
            'source_type' => ['nullable', 'string', 'max:120'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin() && (int) $validated['farm_id'] !== (int) $user->farm_id) {
            return $this->forbiddenError('You do not have permission to create cost records for this farm.');
        }

        $validated['recorded_by_user_id'] = $user->id;

        $record = $this->costingService->createCostRecord($validated);

        return $this->success($record->load(['farm', 'productionPlan', 'plantingBatch', 'recordedBy']), 201);
    }
}
