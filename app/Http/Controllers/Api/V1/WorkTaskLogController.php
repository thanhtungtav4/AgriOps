<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\FarmingLog;
use App\Models\WorkTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkTaskLogController extends Controller
{
    use ApiResponse;

    public function store(Request $request, int $taskId): JsonResponse
    {
        $user = $request->user();
        $task = WorkTask::with(['plantingBatch', 'allocation', 'plot', 'bed'])->findOrFail($taskId);

        if (!$user->isAdmin() && $task->farm_id !== $user->farm_id) {
            return $this->forbiddenError(
                'You do not have permission to log this task.',
                [
                    'required_role' => 'admin or own farm',
                    'current_role' => $user->role,
                ]
            );
        }

        if ($task->status === 'cancelled') {
            return $this->domainError('Cannot submit a log for a cancelled task.', [], 'TASK_CANCELLED');
        }

        if (
            ($task->metadata['requires_photo'] ?? false)
            && empty($request->input('photo_paths'))
            && !$request->hasFile('photos')
        ) {
            return $this->validationError('photo_paths', 'photo_paths is required for this task.');
        }

        $validated = $request->validate([
            'logged_at' => ['nullable', 'date'],
            'actual_start_at' => ['nullable', 'date'],
            'actual_end_at' => ['nullable', 'date', 'after_or_equal:actual_start_at'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo_paths' => ['nullable', 'array'],
            'photo_paths.*' => ['string', 'max:2048'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'image', 'max:5120'],
            'metadata' => ['nullable', 'array'],
            'client_uuid' => ['nullable', 'string', 'max:120'],
        ]);

        if (!empty($validated['client_uuid'])) {
            $existingLog = FarmingLog::where('work_task_id', $task->id)
                ->where('client_uuid', $validated['client_uuid'])
                ->first();

            if ($existingLog) {
                return $this->success(
                    $existingLog->load(['workTask', 'plantingBatch', 'plot', 'bed', 'reportedByUser']),
                    200
                );
            }
        }

        $photoPaths = $validated['photo_paths'] ?? [];

        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = '/storage/' . $photo->store("work-task-logs/{$task->id}", 'public');
        }

        $log = FarmingLog::create([
            'farm_id' => $task->farm_id,
            'work_task_id' => $task->id,
            'planting_batch_id' => $task->planting_batch_id,
            'planting_batch_allocation_id' => $task->planting_batch_allocation_id,
            'plot_id' => $task->plot_id,
            'bed_id' => $task->bed_id,
            'reported_by_user_id' => $user->id,
            'client_uuid' => $validated['client_uuid'] ?? null,
            'logged_at' => $validated['logged_at'] ?? now(),
            'actual_start_at' => $validated['actual_start_at'] ?? null,
            'actual_end_at' => $validated['actual_end_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'photo_paths' => $photoPaths,
            'metadata' => $validated['metadata'] ?? [],
        ]);

        $taskUpdates = [];

        if ($task->status !== 'done') {
            $taskUpdates['status'] = 'done';
        }

        if (!$task->completed_at) {
            $taskUpdates['completed_at'] = $validated['actual_end_at'] ?? now();
        }

        if (!empty($validated['notes'])) {
            $taskUpdates['completion_note'] = $validated['notes'];
        }

        if ($taskUpdates) {
            $task->update($taskUpdates);
        }

        return $this->success(
            $log->load(['workTask', 'plantingBatch', 'plot', 'bed', 'reportedByUser']),
            201
        );
    }
}
