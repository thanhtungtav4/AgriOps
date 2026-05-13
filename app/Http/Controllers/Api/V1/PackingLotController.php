<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Services\PackingLotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PackingLotController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PackingLotService $packingLotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PackingLot::with(['sources.harvestLot', 'sources.farm', 'farm', 'createdBy']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        return $this->success($query->orderByDesc('packed_at')->get());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $lot = PackingLot::with(['sources.harvestLot', 'sources.farm', 'farm', 'createdBy'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $lot->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this packing lot.');
        }

        return $this->success($lot);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'packed_at' => ['required', 'date'],
            'code' => ['nullable', 'string', 'max:80'],
            'total_output_quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'sources' => ['required', 'array', 'min:1'],
            'sources.*.harvest_lot_id' => ['required', 'exists:harvest_lots,id'],
            'sources.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'sources.*.unit' => ['nullable', 'string', 'max:20'],
            'sources.*.metadata' => ['nullable', 'array'],
        ]);

        if (!$user->isAdmin()) {
            foreach ($validated['sources'] as $source) {
                $harvestLot = HarvestLot::find($source['harvest_lot_id']);
                if ($harvestLot && $harvestLot->farm_id !== $user->farm_id) {
                    return $this->forbiddenError('You do not have permission to pack harvest lots from other farms.');
                }
            }
        }

        try {
            $packingLot = $this->packingLotService->create($user, $validated);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], $this->mapDomainCode($e->getMessage()));
        }

        return $this->success(
            $packingLot->load(['sources.harvestLot', 'sources.farm', 'farm', 'createdBy']),
            201
        );
    }

    private function mapDomainCode(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'at least one source') || str_contains($message, 'must have at least')) {
            return 'PACKING_EMPTY_SOURCES';
        }

        if (str_contains($message, 'duplicate')) {
            return 'PACKING_DUPLICATE_SOURCES';
        }

        if (str_contains($message, 'not available') || str_contains($message, 'current status')) {
            return 'PACKING_SOURCE_UNAVAILABLE';
        }

        if (str_contains($message, 'greater than zero')) {
            return 'PACKING_INVALID_QUANTITY';
        }

        if (str_contains($message, 'cannot exceed')) {
            return 'PACKING_QUANTITY_EXCEEDS';
        }

        return 'PACKING_VALIDATION_FAILED';
    }
}
