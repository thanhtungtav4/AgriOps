<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Services\PreHarvestInspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PreHarvestInspectionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PreHarvestInspectionService $inspectionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PreHarvestInspection::with(['plantingBatch.crop', 'plot', 'inspector', 'approvedBy']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('planting_batch_id')) {
            $query->where('planting_batch_id', $request->integer('planting_batch_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success($query->orderByDesc('inspected_at')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $inspection = PreHarvestInspection::with(['plantingBatch.crop', 'plot', 'inspector', 'approvedBy'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $inspection->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this inspection.');
        }

        return $this->success($inspection);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'planting_batch_id' => ['required', 'exists:planting_batches,id'],
            'plot_id' => ['nullable', 'exists:plots,id'],
            'inspected_at' => ['nullable', 'date'],
            'checklist' => ['required', 'array', 'min:1'],
            'checklist.*.key' => ['required', 'string', 'max:100'],
            'checklist.*.label' => ['required', 'string', 'max:255'],
            'checklist.*.passed' => ['required', 'boolean'],
            'checklist.*.note' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $batch = PlantingBatch::findOrFail($validated['planting_batch_id']);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to inspect this batch.');
        }

        $inspection = $this->inspectionService->createInspection($batch, $user, $validated);

        return $this->success($inspection->load(['plantingBatch.crop', 'plot', 'inspector', 'approvedBy']), 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $inspection = PreHarvestInspection::findOrFail($id);
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('Only approver roles can approve pre-harvest inspections.');
        }

        if (!$user->isAdmin() && $inspection->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to approve this inspection.');
        }

        try {
            $inspection = $this->inspectionService->approve($inspection, $user);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], 'INSPECTION_APPROVAL_BLOCKED');
        }

        return $this->success($inspection->load(['plantingBatch.crop', 'plot', 'inspector', 'approvedBy']));
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $inspection = PreHarvestInspection::findOrFail($id);
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('Only approver roles can reject pre-harvest inspections.');
        }

        if (!$user->isAdmin() && $inspection->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to reject this inspection.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        try {
            $inspection = $this->inspectionService->reject($inspection, $user, $validated['reason']);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], 'INSPECTION_REJECTION_BLOCKED');
        }

        return $this->success($inspection->load(['plantingBatch.crop', 'plot', 'inspector', 'approvedBy']));
    }
}
