<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PlantingBatch;
use App\Services\PlantingBatchLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PlantingBatchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PlantingBatchLifecycleService $lifecycleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PlantingBatch::with(['farm', 'crop', 'variety']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('crop_id')) {
            $query->where('crop_id', $request->integer('crop_id'));
        }

        return $this->success($query->orderByDesc('created_at')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $batch = PlantingBatch::with(['farm', 'crop', 'variety', 'allocations.plot', 'allocations.bed'])->findOrFail($id);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($batch);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->farm_id) {
            return $this->forbiddenError('User must be assigned to a farm to create planting batches.');
        }

        $validated = $request->validate([
            'farm_id' => ['nullable', 'exists:farms,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'production_plan_id' => ['nullable', 'exists:production_plans,id'],
            'code' => ['nullable', 'string', 'max:50'],
            'planned_quantity' => ['nullable', 'numeric', 'min:0'],
            'planned_unit' => ['nullable', 'string', 'max:20'],
            'planned_area_m2' => ['nullable', 'numeric', 'min:0'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_harvest_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin()) {
            if (isset($validated['farm_id']) && $validated['farm_id'] !== $user->farm_id) {
                return $this->forbiddenError(
                    'You cannot create planting batches for other farms.',
                    [
                        'required_farm_id' => $user->farm_id,
                        'provided_farm_id' => $validated['farm_id'],
                    ]
                );
            }
            $validated['farm_id'] = $user->farm_id;
        }

        $batch = PlantingBatch::create($validated);

        return $this->success($batch->load(['farm', 'crop', 'variety']), 201);
    }

    public function transition(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $batch = PlantingBatch::findOrFail($id);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to modify this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        $validated = $request->validate([
            'to_status' => [
                'required',
                'string',
                Rule::in($this->lifecycleService->getAllowedStatuses()),
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['to_status'] === 'approved' && !$user->canApprove()) {
            return $this->forbiddenError('Only approver roles can approve planting batches.');
        }

        try {
            $batch = $this->lifecycleService->transition(
                $batch,
                $validated['to_status'],
                $validated['reason'] ?? null
            );

            return $this->success($batch->load(['farm', 'crop', 'variety']));
        } catch (InvalidArgumentException $e) {
            $code = $this->mapExceptionToErrorCode($e);
            return $this->domainError($e->getMessage(), [], $code);
        }
    }

    private function mapExceptionToErrorCode(InvalidArgumentException $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'cancellation reason')) {
            return 'CANCELLATION_REASON_REQUIRED';
        }

        if (str_contains($message, 'terminal')) {
            return 'INVALID_TRANSITION';
        }

        if (str_contains($message, 'invalid transition')) {
            return 'INVALID_TRANSITION';
        }

        return 'INVALID_TRANSITION';
    }
}
