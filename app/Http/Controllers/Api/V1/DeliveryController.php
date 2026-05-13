<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\DeliveryNote;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DeliveryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DeliveryService $deliveryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = DeliveryNote::with(['packingLot', 'supplyContract', 'acceptanceRecord', 'returnRecords']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        return $this->success($query->orderByDesc('delivered_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'packing_lot_id' => ['required', 'exists:packing_lots,id'],
            'supply_contract_id' => ['nullable', 'exists:supply_contracts,id'],
            'code' => ['nullable', 'string', 'max:80'],
            'customer_name' => ['required_without:supply_contract_id', 'nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', 'string', 'max:80'],
            'delivered_at' => ['required', 'date'],
            'planned_quantity' => ['required', 'numeric', 'min:0.001'],
            'accepted_quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'side_channel_revenue' => ['nullable', 'numeric', 'min:0'],
            'accepted_by_name' => ['nullable', 'string', 'max:255'],
            'accepted_at' => ['nullable', 'date'],
            'evidence_photo_paths' => ['nullable', 'array'],
            'evidence_photo_paths.*' => ['string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'acceptance_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $delivery = $this->deliveryService->createDelivery($request->user(), $validated);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], $this->mapDomainCode($e->getMessage()));
        }

        return $this->success(
            $delivery->load(['packingLot', 'supplyContract', 'acceptanceRecord', 'returnRecords']),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $delivery = DeliveryNote::with(['packingLot', 'supplyContract', 'acceptanceRecord', 'returnRecords'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $delivery->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this delivery.');
        }

        return $this->success($delivery);
    }

    public function revenue(Request $request, int $id): JsonResponse
    {
        $delivery = DeliveryNote::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $delivery->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this delivery revenue.');
        }

        return $this->success($this->deliveryService->revenueSnapshot($delivery));
    }

    private function mapDomainCode(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'permission')) {
            return 'DELIVERY_FORBIDDEN';
        }

        if (str_contains($message, 'packing lot')) {
            return 'DELIVERY_PACKING_LOT_INVALID';
        }

        if (str_contains($message, 'quantity')) {
            return 'DELIVERY_QUANTITY_INVALID';
        }

        return 'DELIVERY_VALIDATION_FAILED';
    }
}
