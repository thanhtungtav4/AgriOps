<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ReturnRecord;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ReturnRecordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DeliveryService $deliveryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ReturnRecord::with(['deliveryNote', 'packingLot', 'recordedBy']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        return $this->success($query->orderByDesc('returned_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delivery_note_id' => ['required', 'exists:delivery_notes,id'],
            'returned_at' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['nullable', 'string', 'max:20'],
            'reason' => ['required', Rule::in(ReturnRecord::REASONS)],
            'handling_action' => ['required', Rule::in(ReturnRecord::HANDLING_ACTIONS)],
            'side_channel_revenue' => ['nullable', 'numeric', 'min:0'],
            'evidence_photo_paths' => ['nullable', 'array'],
            'evidence_photo_paths.*' => ['string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $return = $this->deliveryService->createReturn($request->user(), $validated);
        } catch (InvalidArgumentException $e) {
            return $this->domainError($e->getMessage(), [], $this->mapDomainCode($e->getMessage()));
        }

        return $this->success(
            $return->load(['deliveryNote', 'packingLot', 'recordedBy']),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $return = ReturnRecord::with(['deliveryNote', 'packingLot', 'recordedBy'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $return->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to access this return record.');
        }

        return $this->success($return);
    }

    private function mapDomainCode(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'permission')) {
            return 'RETURN_FORBIDDEN';
        }

        if (str_contains($message, 'quantity')) {
            return 'RETURN_QUANTITY_INVALID';
        }

        return 'RETURN_VALIDATION_FAILED';
    }
}
