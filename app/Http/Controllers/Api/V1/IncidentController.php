<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Incident;
use App\Models\PlantingBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Incident::with(['plantingBatch.crop', 'plot', 'bed', 'reportedByUser']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('planting_batch_id')) {
            $query->where('planting_batch_id', $request->integer('planting_batch_id'));
        }

        return $this->success($query->orderByDesc('detected_at')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $incident = Incident::with(['chemicalUsages', 'plantingBatch.crop', 'plot', 'bed', 'reportedByUser'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $incident->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this incident.');
        }

        return $this->success($incident);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_id' => ['nullable', 'exists:farms,id'],
            'planting_batch_id' => ['nullable', 'exists:planting_batches,id'],
            'work_task_id' => ['nullable', 'exists:work_tasks,id'],
            'farming_log_id' => ['nullable', 'exists:farming_logs,id'],
            'plot_id' => ['nullable', 'exists:plots,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'incident_type' => ['required', Rule::in(Incident::TYPES)],
            'severity' => ['nullable', Rule::in(Incident::SEVERITIES)],
            'status' => ['nullable', Rule::in(Incident::STATUSES)],
            'detected_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'treatment_note' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin()) {
            $validated['farm_id'] = $user->farm_id;
        }

        if (empty($validated['farm_id'])) {
            return $this->validationError('farm_id', 'farm_id is required for admin-created incidents.');
        }

        if (!empty($validated['planting_batch_id'])) {
            $batch = PlantingBatch::findOrFail($validated['planting_batch_id']);

            if ($batch->farm_id !== $validated['farm_id']) {
                return $this->domainError('Planting batch does not belong to the incident farm.', [], 'FARM_MISMATCH');
            }
        }

        $incident = Incident::create([
            ...$validated,
            'reported_by_user_id' => $user->id,
            'detected_at' => $validated['detected_at'] ?? now(),
        ]);

        return $this->success($incident->load(['plantingBatch.crop', 'plot', 'bed', 'reportedByUser']), 201);
    }
}
