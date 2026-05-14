<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\IrrigationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IrrigationLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = IrrigationLog::with(['plantingBatch', 'plot', 'loggedBy']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->filled('planting_batch_id')) {
            $query->where('planting_batch_id', $request->input('planting_batch_id'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('irrigated_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('irrigated_at', '<=', $request->input('to_date'));
        }

        $logs = $query->orderByDesc('irrigated_at')->paginate($request->input('per_page', 50));

        return $this->success($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to log irrigation.');
        }

        $validated = $request->validate([
            'planting_batch_id' => 'nullable|exists:planting_batches,id',
            'plot_id' => 'nullable|exists:plots,id',
            'bed_id' => 'nullable|exists:beds,id',
            'irrigated_at' => 'required|date',
            'planned_quantity' => 'nullable|numeric|min:0',
            'actual_quantity' => 'nullable|numeric|min:0',
            'method' => 'nullable|in:drip,sprinkler,manual,flood,other',
            'duration_minutes' => 'nullable|integer|min:0',
            'water_source' => 'nullable|string|max:100',
            'ph' => 'nullable|numeric|min:0|max:14',
            'temperature' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['farm_id'] = $user->farm_id ?? $request->input('farm_id');
        $validated['logged_by'] = $user->id;

        $log = IrrigationLog::create($validated);

        return $this->success($log->load(['plantingBatch', 'plot', 'loggedBy']), 201, 'Irrigation logged');
    }

    public function show(int $id): JsonResponse
    {
        $log = IrrigationLog::with(['plantingBatch', 'plot', 'bed', 'loggedBy'])->findOrFail($id);

        return $this->success($log);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $log = IrrigationLog::findOrFail($id);

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to update irrigation log.');
        }

        $validated = $request->validate([
            'actual_quantity' => 'nullable|numeric|min:0',
            'method' => 'nullable|in:drip,sprinkler,manual,flood,other',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $log->update($validated);

        return $this->success($log->fresh()->load(['plantingBatch', 'plot', 'loggedBy']), 200, 'Irrigation log updated');
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete irrigation logs.');
        }

        $log = IrrigationLog::findOrFail($id);
        $log->delete();

        return $this->success(null, 200, 'Irrigation log deleted');
    }
}
