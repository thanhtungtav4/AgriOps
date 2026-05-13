<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Services\HarvestLotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class HarvestLotController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly HarvestLotService $harvestLotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = HarvestLot::with(['plantingBatch.crop', 'preHarvestInspection', 'plot', 'bed', 'harvestedBy']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->has('planting_batch_id')) {
            $query->where('planting_batch_id', $request->integer('planting_batch_id'));
        }

        return $this->success($query->orderByDesc('harvest_date')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $lot = HarvestLot::with(['plantingBatch.crop', 'preHarvestInspection', 'plot', 'bed', 'harvestedBy'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $lot->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this harvest lot.');
        }

        return $this->success($lot);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'planting_batch_id' => ['required', 'exists:planting_batches,id'],
            'work_task_id' => ['nullable', 'exists:work_tasks,id'],
            'plot_id' => ['nullable', 'exists:plots,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'code' => ['nullable', 'string', 'max:80'],
            'harvest_date' => ['required', 'date'],
            'raw_quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['nullable', 'string', 'max:20'],
            'grade_a_quantity' => ['nullable', 'numeric', 'min:0'],
            'grade_b_quantity' => ['nullable', 'numeric', 'min:0'],
            'grade_c_quantity' => ['nullable', 'numeric', 'min:0'],
            'reject_quantity' => ['nullable', 'numeric', 'min:0'],
            'reject_reasons' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(HarvestLot::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $batch = PlantingBatch::findOrFail($validated['planting_batch_id']);

        if (!$user->isAdmin() && $batch->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to harvest this batch.');
        }

        try {
            $lot = $this->harvestLotService->create($batch, $user, $validated);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], $this->mapDomainCode($e));
        }

        return $this->success($lot->load(['plantingBatch.crop', 'preHarvestInspection', 'plot', 'bed', 'harvestedBy']), 201);
    }

    private function mapDomainCode(InvalidArgumentException $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'inspection')) {
            return 'HARVEST_INSPECTION_BLOCKED';
        }

        if (str_contains($message, 'isolation')) {
            return 'HARVEST_ISOLATION_BLOCKED';
        }

        return 'HARVEST_VALIDATION_FAILED';
    }
}
