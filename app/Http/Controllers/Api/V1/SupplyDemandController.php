<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\SupplyDemand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplyDemandController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SupplyDemand::with(['contract', 'crop']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('supply_contract_id')) {
            $query->where('supply_contract_id', $request->integer('supply_contract_id'));
        }

        if ($request->has('crop_id')) {
            $query->where('crop_id', $request->integer('crop_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success($query->orderBy('target_date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('User must be assigned to a farm to create demands.');
        }

        $validated = $request->validate([
            'supply_contract_id' => ['nullable', 'exists:supply_contracts,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', Rule::in(['kg', 'trái', 'bó', 'thùng'])],
            'frequency' => ['nullable', Rule::in(['once', 'daily', 'weekly', 'monthly', 'seasonal'])],
            'target_date' => ['required', 'date'],
            'farm_id' => ['nullable', 'exists:farms,id'],
            'status' => ['nullable', Rule::in(['pending', 'planned', 'fulfilled'])],
            'notes' => ['nullable', 'string'],
        ]);

        if (!$user->isAdmin()) {
            $validated['farm_id'] = $user->farm_id;
        }

        $demand = SupplyDemand::create($validated);

        return $this->success($demand->refresh()->load(['contract', 'crop']), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $demand = SupplyDemand::with(['contract', 'crop', 'productionPlans'])->findOrFail($id);

        if (!$user->isAdmin() && $demand->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($demand);
    }
}
