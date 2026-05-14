<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\CrossFarmAllocation;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrossFarmAllocationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CrossFarmAllocation::with(['sourceFarm', 'supplementFarm', 'crop']);

        if (!$user->isAdmin()) {
            $query->where(fn($q) => $q->where('source_farm_id', $user->farm_id)->orWhere('supplement_farm_id', $user->farm_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('source_farm_id')) {
            $query->where('source_farm_id', $request->input('source_farm_id'));
        }

        $allocations = $query->latest()->paginate($request->input('per_page', 20));

        return $this->success($allocations);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])) {
            return $this->forbiddenError('You do not have permission to create cross-farm allocations.');
        }

        $validated = $request->validate([
            'source_farm_id' => 'required|exists:farms,id',
            'supplement_farm_id' => 'required|exists:farms,id',
            'production_plan_id' => 'nullable|exists:production_plans,id',
            'planting_batch_id' => 'nullable|exists:planting_batches,id',
            'crop_id' => 'nullable|exists:crops,id',
            'requested_quantity' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['requested_by'] = $user->id;
        $validated['requested_at'] = now();
        $validated['status'] = CrossFarmAllocation::STATUSES['pending'];

        $allocation = CrossFarmAllocation::create($validated);

        return $this->success(
            $allocation->load(['sourceFarm', 'supplementFarm', 'crop']),
            201,
            'Cross-farm allocation request created'
        );
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('You do not have permission to approve.');
        }

        $allocation = CrossFarmAllocation::findOrFail($id);

        if ($allocation->status !== CrossFarmAllocation::STATUSES['pending']) {
            return $this->domainError('Only pending allocations can be approved.', [], 'INVALID_STATUS', 422);
        }

        $validated = $request->validate([
            'allocated_quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $allocation->update([
            'status' => CrossFarmAllocation::STATUSES['approved'],
            'approved_by' => $user->id,
            'approved_at' => now(),
            'allocated_quantity' => $validated['allocated_quantity'] ?? $allocation->requested_quantity,
            'notes' => $validated['notes'] ?? $allocation->notes,
        ]);

        return $this->success(
            $allocation->fresh()->load(['sourceFarm', 'supplementFarm', 'crop']),
            200,
            'Cross-farm allocation approved'
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('You do not have permission to reject.');
        }

        $allocation = CrossFarmAllocation::findOrFail($id);

        if ($allocation->status !== CrossFarmAllocation::STATUSES['pending']) {
            return $this->domainError('Only pending allocations can be rejected.', [], 'INVALID_STATUS', 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $allocation->update([
            'status' => CrossFarmAllocation::STATUSES['rejected'],
            'approved_by' => $user->id,
            'approved_at' => now(),
            'notes' => $validated['reason'],
        ]);

        return $this->success(
            $allocation->fresh()->load(['sourceFarm', 'supplementFarm', 'crop']),
            200,
            'Cross-farm allocation rejected'
        );
    }

    public function fulfill(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'warehouse'])) {
            return $this->forbiddenError('You do not have permission to mark as fulfilled.');
        }

        $allocation = CrossFarmAllocation::findOrFail($id);

        if ($allocation->status !== CrossFarmAllocation::STATUSES['approved']) {
            return $this->domainError('Only approved allocations can be fulfilled.', [], 'INVALID_STATUS', 422);
        }

        $validated = $request->validate([
            'packing_lot_code' => 'nullable|string|max:100',
            'qr_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $allocation->update([
            'status' => CrossFarmAllocation::STATUSES['fulfilled'],
            'packing_lot_code' => $validated['packing_lot_code'] ?? null,
            'qr_code' => $validated['qr_code'] ?? null,
            'notes' => $validated['notes'] ?? $allocation->notes,
        ]);

        return $this->success(
            $allocation->fresh()->load(['sourceFarm', 'supplementFarm', 'crop']),
            200,
            'Cross-farm allocation fulfilled'
        );
    }

    public function availableFarms(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('You are not assigned to a farm.');
        }

        $query = Farm::query();

        if (!$user->isAdmin()) {
            $query->where('id', '!=', $user->farm_id);
        }

        $farms = $query->where('is_active', true)->get(['id', 'name', 'code']);

        return $this->success($farms);
    }
}
