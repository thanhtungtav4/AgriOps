<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Bed;
use App\Models\Plot;
use App\Models\SoilHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoilHistoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SoilHistory::with(['plot', 'bed', 'recordedBy']);

        // Non-admin users can only see their farm's records
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('plot', function ($q2) use ($user) {
                    $q2->where('farm_id', $user->farm_id);
                })
                ->orWhereHas('bed', function ($q2) use ($user) {
                    $q2->whereHas('plot', function ($q3) use ($user) {
                        $q3->where('farm_id', $user->farm_id);
                    });
                });
                // For records where both plot_id and bed_id are null, they are excluded for non-admins
                // Only admin can see global records
            });
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->input('plot_id'));
        }

        if ($request->filled('bed_id')) {
            $query->where('bed_id', $request->input('bed_id'));
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->input('record_type'));
        }

        if ($request->filled('from_date')) {
            $query->where('recorded_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('recorded_at', '<=', $request->input('to_date'));
        }

        $records = $query->orderByDesc('recorded_at')->paginate($request->input('per_page', 50));

        return $this->success($records);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to record soil history.');
        }

        $validated = $request->validate([
            'plot_id' => 'nullable|exists:plots,id',
            'bed_id' => 'nullable|exists:beds,id',
            'record_type' => 'required|in:test_result,amendment,reading',
            'recorded_at' => 'required|date',
            'ph' => 'nullable|numeric|min:0|max:14',
            'nitrogen' => 'nullable|numeric|min:0',
            'phosphorus' => 'nullable|numeric|min:0',
            'potassium' => 'nullable|numeric|min:0',
            'organic_matter' => 'nullable|numeric|min:0|max:100',
            'moisture' => 'nullable|numeric|min:0|max:100',
            'zinc' => 'nullable|numeric|min:0',
            'iron' => 'nullable|numeric|min:0',
            'manganese' => 'nullable|numeric|min:0',
            'copper' => 'nullable|numeric|min:0',
            'boron' => 'nullable|numeric|min:0',
            'amendment_applied' => 'nullable|string|max:255',
            'amendment_quantity' => 'nullable|numeric|min:0',
            'amendment_unit' => 'nullable|string|max:50',
            'source' => 'nullable|in:lab,manual',
            'lab_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Validate that plot_id and bed_id belong to the user's farm
        if (isset($validated['plot_id']) && $validated['plot_id']) {
            $plot = Plot::find($validated['plot_id']);
            if (!$user->isAdmin() && $plot && $plot->farm_id !== $user->farm_id) {
                return $this->forbiddenError('You can only create records for your own farm plots.');
            }
        }

        if (isset($validated['bed_id']) && $validated['bed_id']) {
            $bed = Bed::with('plot')->find($validated['bed_id']);
            if (!$user->isAdmin() && $bed && $bed->plot && $bed->plot->farm_id !== $user->farm_id) {
                return $this->forbiddenError('You can only create records for your own farm beds.');
            }
        }

        if (!$user->isAdmin() && empty($validated['plot_id']) && empty($validated['bed_id'])) {
            return $this->forbiddenError('Soil history records must be associated with your farm plot or bed.');
        }

        $validated['recorded_by'] = $user->id;

        $record = SoilHistory::create($validated);

        return $this->success(
            $record->load(['plot', 'bed', 'recordedBy']),
            201,
            'Soil history record created'
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $record = SoilHistory::with(['plot', 'bed', 'recordedBy'])->findOrFail($id);
        $user = $request->user();

        // Non-admin users can only view records from their farm
        if (!$user->isAdmin()) {
            $hasAccess = false;
            
            if ($record->plot_id) {
                $hasAccess = $record->plot && $record->plot->farm_id === $user->farm_id;
            } elseif ($record->bed_id) {
                $hasAccess = $record->bed && $record->bed->plot && $record->bed->plot->farm_id === $user->farm_id;
            }
            
            if (!$hasAccess) {
                return $this->forbiddenError('You do not have permission to view this record.');
            }
        }

        return $this->success($record);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = SoilHistory::findOrFail($id);
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'farm_owner', 'farm_manager', 'technician'])) {
            return $this->forbiddenError('You do not have permission to update soil history.');
        }

        // Non-admin users can only update records from their farm
        if (!$user->isAdmin()) {
            $hasAccess = false;
            
            if ($record->plot_id) {
                $hasAccess = $record->plot && $record->plot->farm_id === $user->farm_id;
            } elseif ($record->bed_id) {
                $hasAccess = $record->bed && $record->bed->plot && $record->bed->plot->farm_id === $user->farm_id;
            }
            
            if (!$hasAccess) {
                return $this->forbiddenError('You do not have permission to update this record.');
            }
        }

        $validated = $request->validate([
            'record_type' => 'nullable|in:test_result,amendment,reading',
            'recorded_at' => 'nullable|date',
            'ph' => 'nullable|numeric|min:0|max:14',
            'nitrogen' => 'nullable|numeric|min:0',
            'phosphorus' => 'nullable|numeric|min:0',
            'potassium' => 'nullable|numeric|min:0',
            'organic_matter' => 'nullable|numeric|min:0|max:100',
            'moisture' => 'nullable|numeric|min:0|max:100',
            'zinc' => 'nullable|numeric|min:0',
            'iron' => 'nullable|numeric|min:0',
            'manganese' => 'nullable|numeric|min:0',
            'copper' => 'nullable|numeric|min:0',
            'boron' => 'nullable|numeric|min:0',
            'amendment_applied' => 'nullable|string|max:255',
            'amendment_quantity' => 'nullable|numeric|min:0',
            'amendment_unit' => 'nullable|string|max:50',
            'source' => 'nullable|in:lab,manual',
            'lab_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record->update($validated);

        return $this->success(
            $record->fresh()->load(['plot', 'bed', 'recordedBy']),
            200,
            'Soil history record updated'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $record = SoilHistory::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete soil history records.');
        }

        $record->delete();

        return $this->success(null, 200, 'Soil history record deleted');
    }
}
