<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\PriceTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PriceTableController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = PriceTable::with(['farm', 'crop', 'variety']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where(function ($query) use ($user) {
                $query->where('farm_id', $user->farm_id)
                    ->orWhereNull('farm_id');
            });
        }

        if ($request->has('crop_id')) {
            $query->where('crop_id', $request->integer('crop_id'));
        }

        if ($request->has('unit')) {
            $query->where('unit', $request->string('unit'));
        }

        return $this->success($query->orderByDesc('effective_from')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_id' => ['nullable', 'exists:farms,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'unit' => ['required', Rule::in(['kg', 'trái', 'bó', 'thùng'])],
            'grade_a_price' => ['required', 'numeric', 'min:0'],
            'grade_b_price' => ['nullable', 'numeric', 'min:0'],
            'grade_c_price' => ['nullable', 'numeric', 'min:0'],
            'side_channel_price' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin()) {
            $validated['farm_id'] ??= $user->farm_id;

            if ((int) $validated['farm_id'] !== (int) $user->farm_id) {
                return $this->forbiddenError('You do not have permission to create prices for this farm.');
            }
        }

        $price = PriceTable::create($validated);

        return $this->success($price->load(['farm', 'crop', 'variety']), 201);
    }
}
