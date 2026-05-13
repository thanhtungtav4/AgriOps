<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ChemicalUsage;
use App\Models\Incident;
use App\Models\PlantingBatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChemicalUsageController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = ChemicalUsage::with(['incident', 'plantingBatch.crop', 'plot', 'bed', 'appliedByUser']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('planting_batch_id')) {
            $query->where('planting_batch_id', $request->integer('planting_batch_id'));
        }

        return $this->success($query->orderByDesc('applied_at')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $usage = ChemicalUsage::with(['incident', 'plantingBatch.crop', 'plot', 'bed', 'appliedByUser'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $usage->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this chemical usage.');
        }

        return $this->success($usage);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_id' => ['nullable', 'exists:farms,id'],
            'incident_id' => ['nullable', 'exists:incidents,id'],
            'planting_batch_id' => ['nullable', 'exists:planting_batches,id'],
            'work_task_id' => ['nullable', 'exists:work_tasks,id'],
            'plot_id' => ['nullable', 'exists:plots,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_type' => ['nullable', Rule::in(ChemicalUsage::PRODUCT_TYPES)],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'dosage_value' => ['nullable', 'numeric', 'min:0'],
            'dosage_unit' => ['nullable', 'string', 'max:30'],
            'quantity_value' => ['nullable', 'numeric', 'min:0'],
            'quantity_unit' => ['nullable', 'string', 'max:30'],
            'cost_amount' => ['nullable', 'numeric', 'min:0'],
            'isolation_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'applied_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin()) {
            $validated['farm_id'] = $user->farm_id;
        }

        if (empty($validated['farm_id'])) {
            return $this->validationError('farm_id', 'farm_id is required for admin-created chemical usages.');
        }

        if (empty($validated['incident_id']) && empty($validated['planting_batch_id'])) {
            return $this->validationError('planting_batch_id', 'Either incident_id or planting_batch_id is required.');
        }

        if (!empty($validated['incident_id'])) {
            $incident = Incident::findOrFail($validated['incident_id']);

            if ($incident->farm_id !== $validated['farm_id']) {
                return $this->domainError('Incident does not belong to the usage farm.', [], 'FARM_MISMATCH');
            }

            $validated['planting_batch_id'] ??= $incident->planting_batch_id;
            $validated['plot_id'] ??= $incident->plot_id;
            $validated['bed_id'] ??= $incident->bed_id;
        }

        if (!empty($validated['planting_batch_id'])) {
            $batch = PlantingBatch::findOrFail($validated['planting_batch_id']);

            if ($batch->farm_id !== $validated['farm_id']) {
                return $this->domainError('Planting batch does not belong to the usage farm.', [], 'FARM_MISMATCH');
            }
        }

        $appliedAt = isset($validated['applied_at']) ? Carbon::parse($validated['applied_at']) : now();
        $isolationDays = $validated['isolation_days'] ?? 0;

        $usage = ChemicalUsage::create([
            ...$validated,
            'applied_by_user_id' => $user->id,
            'applied_at' => $appliedAt,
            'isolation_days' => $isolationDays,
            'isolation_ends_at' => $isolationDays > 0 ? $appliedAt->copy()->addDays($isolationDays) : null,
        ]);

        return $this->success($usage->load(['incident', 'plantingBatch.crop', 'plot', 'bed', 'appliedByUser']), 201);
    }
}
