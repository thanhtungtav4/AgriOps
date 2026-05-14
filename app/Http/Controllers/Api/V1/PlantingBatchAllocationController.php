<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\AllocationException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use App\Services\PlantingBatchAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantingBatchAllocationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PlantingBatchAllocationService $allocationService
    ) {}

    public function store(Request $request, int $batchId): JsonResponse
    {
        $user = $request->user();
        $batch = PlantingBatch::findOrFail($batchId);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to allocate land for this batch.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        $validated = $request->validate([
            'plot_id' => ['required', 'exists:plots,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'allocated_area_m2' => ['nullable', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plot = Plot::findOrFail($validated['plot_id']);

        try {
            $allocation = $this->allocationService->allocate(
                $batch,
                $plot,
                [
                    'allocated_area_m2' => $validated['allocated_area_m2'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                $validated['bed_id'] ?? null,
                $user->id
            );

            return $this->success(
                $allocation->load(['plot', 'bed']),
                201,
                'Planting batch allocation created'
            );
        } catch (AllocationException $e) {
            return $this->domainError($e->getMessage(), $e->context, $e->errorCode ?? 'ALLOCATION_ERROR');
        }
    }

    public function destroy(Request $request, int $batchId, int $allocationId): JsonResponse
    {
        $user = $request->user();
        $batch = PlantingBatch::findOrFail($batchId);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to remove allocations for this batch.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        $allocation = PlantingBatchAllocation::where('planting_batch_id', $batch->id)
            ->findOrFail($allocationId);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->allocationService->deallocate($allocation, $validated['reason'] ?? null, $user->id);

        return $this->success(null, 200, 'Planting batch allocation removed');
    }
}

