<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\FertilizerLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FertilizerLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = FertilizerLog::with(['plantingBatch', 'plot', 'loggedBy']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->filled('planting_batch_id')) {
            $query->where('planting_batch_id', $request->input('planting_batch_id'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('fertilized_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('fertilized_at', '<=', $request->input('to_date'));
        }

        $logs = $query->orderByDesc('fertilized_at')->paginate($request->input('per_page', 50));

        return $this->success($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to log fertilizer application.');
        }

        $validated = $request->validate([
            'planting_batch_id' => 'nullable|exists:planting_batches,id',
            'plot_id' => 'nullable|exists:plots,id',
            'bed_id' => 'nullable|exists:beds,id',
            'fertilized_at' => 'required|date',
            'fertilizer_name' => 'nullable|string|max:255',
            'fertilizer_type' => 'nullable|string|max:100',
            'planned_quantity' => 'nullable|numeric|min:0',
            'actual_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'nitrogen_percent' => 'nullable|numeric|min:0|max:100',
            'phosphorus_percent' => 'nullable|numeric|min:0|max:100',
            'potassium_percent' => 'nullable|numeric|min:0|max:100',
            'method' => 'nullable|in:broadcast,drip,foliar,injection,manual,other',
            'cost' => 'nullable|numeric|min:0',
            'weather_condition' => 'nullable|string|max:50',
            'soil_moisture_before' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['farm_id'] = $user->farm_id ?? $request->input('farm_id');
        $validated['logged_by'] = $user->id;

        $log = FertilizerLog::create($validated);

        return $this->success($log->load(['plantingBatch', 'plot', 'loggedBy']), 201, 'Fertilizer application logged');
    }

    public function show(int $id): JsonResponse
    {
        $log = FertilizerLog::with(['plantingBatch', 'plot', 'bed', 'loggedBy'])->findOrFail($id);

        return $this->success($log);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $log = FertilizerLog::findOrFail($id);

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to update fertilizer log.');
        }

        $validated = $request->validate([
            'actual_quantity' => 'nullable|numeric|min:0',
            'method' => 'nullable|in:broadcast,drip,foliar,injection,manual,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $log->update($validated);

        return $this->success($log->fresh()->load(['plantingBatch', 'plot', 'loggedBy']), 200, 'Fertilizer log updated');
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete fertilizer logs.');
        }

        $log = FertilizerLog::findOrFail($id);
        $log->delete();

        return $this->success(null, 200, 'Fertilizer log deleted');
    }
}
