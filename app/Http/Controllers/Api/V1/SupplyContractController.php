<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\SupplyContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplyContractController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SupplyContract::with(['crop', 'productStandard']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('crop_id')) {
            $query->where('crop_id', $request->integer('crop_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success($query->orderByDesc('start_date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('User must be assigned to a farm to create contracts.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_type' => ['nullable', Rule::in(['restaurant', 'wholesale', 'retail', 'export', 'other'])],
            'crop_id' => ['required', 'exists:crops,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', Rule::in(['kg', 'trái', 'bó', 'thùng'])],
            'frequency' => ['nullable', Rule::in(['once', 'daily', 'weekly', 'monthly', 'seasonal'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'product_standard_id' => ['nullable', 'exists:product_standards,id'],
            'farm_id' => ['nullable', 'exists:farms,id'],
            'status' => ['nullable', Rule::in(['active', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ]);

        if (!$user->isAdmin()) {
            $validated['farm_id'] = $user->farm_id;
        }

        $contract = SupplyContract::create($validated);

        return $this->success($contract->refresh()->load(['crop', 'productStandard']), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $contract = SupplyContract::with(['crop', 'productStandard', 'demands', 'productionPlans'])->findOrFail($id);

        if (!$user->isAdmin() && $contract->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($contract);
    }
}
