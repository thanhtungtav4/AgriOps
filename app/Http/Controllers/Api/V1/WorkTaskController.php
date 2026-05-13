<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PlantingBatch;
use App\Models\WorkTask;
use App\Services\WorkTaskGenerationService;
use App\Services\WorkTaskStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class WorkTaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly WorkTaskStatusService $statusService,
        private readonly WorkTaskGenerationService $generationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = WorkTask::with(['plantingBatch.crop', 'plot', 'bed', 'assignedUser']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('assigned_user_id')) {
            $query->where('assigned_user_id', $request->integer('assigned_user_id'));
        }

        if ($request->has('planting_batch_id')) {
            $query->where('planting_batch_id', $request->integer('planting_batch_id'));
        }

        if ($request->has('due_before')) {
            $query->where('planned_due_date', '<=', $request->date('due_before'));
        }

        $tasks = $query
            ->orderBy('planned_due_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return $this->success($tasks);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $task = WorkTask::with([
            'plantingBatch.crop',
            'plot',
            'bed',
            'assignedUser',
            'growthStage',
        ])->findOrFail($id);

        if (!$user->isAdmin() && $task->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        return $this->success($task);
    }

    public function generateWorkTasks(Request $request, int $batchId): JsonResponse
    {
        $user = $request->user();
        $batch = PlantingBatch::findOrFail($batchId);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to access this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        $validated = $request->validate([
            'allocation_id' => ['nullable', 'integer'],
        ]);

        try {
            $result = $this->generationService->generateFromBatch(
                $batch,
                $validated['allocation_id'] ?? null
            );
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], 'INVALID_ALLOCATION');
        }

        $status = $result['created_count'] > 0 ? 201 : 200;

        return response()->json([
            'data' => [
                'tasks' => $result['tasks'],
            ],
            'meta' => [
                'trace_id' => request()->header('X-Trace-ID') ?? uniqid(),
                'created_count' => $result['created_count'],
                'existing_count' => $result['existing_count'],
                'skipped_count' => $result['skipped_count'],
            ],
        ], $status);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $task = WorkTask::findOrFail($id);

        if (!$user->isAdmin() && $task->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to modify this resource.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(['planned', 'assigned', 'in_progress', 'done', 'cancelled']),
            ],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'completion_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $task = $this->statusService->transition(
                $task,
                $validated['status'],
                $validated['assigned_user_id'] ?? null,
                $validated['reason'] ?? null,
                $validated['completion_note'] ?? null
            );

            return $this->success($task->load(['plantingBatch.crop', 'plot', 'bed', 'assignedUser']));
        } catch (InvalidArgumentException $e) {
            return $this->mapExceptionToResponse($e);
        }
    }

    private function mapExceptionToResponse(InvalidArgumentException $e): JsonResponse
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'assigned_user_id is required')) {
            return $this->validationError('assigned_user_id', 'assigned_user_id is required when transitioning to assigned status.');
        }

        if (str_contains($message, 'cancellation reason is required')) {
            return $this->validationError('reason', 'Cancellation reason is required.');
        }

        if (str_contains($message, 'assigned user')) {
            return $this->domainError($e->getMessage(), [], 'INVALID_ASSIGNEE');
        }

        if (str_contains($message, 'invalid transition')) {
            return $this->domainError($e->getMessage(), [], 'INVALID_TRANSITION');
        }

        return $this->domainError($e->getMessage(), [], 'DOMAIN_ERROR');
    }
}
