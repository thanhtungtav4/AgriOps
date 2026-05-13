<?php

namespace App\Services;

use App\Models\DeliveryAcceptanceRecord;
use App\Models\DeliveryNote;
use App\Models\PackingLot;
use App\Models\ReturnRecord;
use App\Models\SupplyContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DeliveryService
{
    public function createDelivery(User $user, array $data): DeliveryNote
    {
        $packingLot = PackingLot::findOrFail($data['packing_lot_id']);
        $this->assertUserCanAccessFarm($user, (int) $packingLot->farm_id);
        $this->assertPackingLotCanDeliver($packingLot);

        $contract = isset($data['supply_contract_id'])
            ? SupplyContract::findOrFail($data['supply_contract_id'])
            : null;

        if ($contract && $contract->farm_id && $contract->farm_id !== $packingLot->farm_id) {
            throw new InvalidArgumentException('Supply contract belongs to a different farm.');
        }

        $acceptedQuantity = (float) ($data['accepted_quantity'] ?? $data['planned_quantity']);
        $plannedQuantity = (float) $data['planned_quantity'];
        $unitPrice = (float) $data['unit_price'];

        if ($acceptedQuantity > $plannedQuantity) {
            throw new InvalidArgumentException('Accepted quantity cannot exceed planned quantity.');
        }

        if ($plannedQuantity > (float) $packingLot->total_output_quantity) {
            throw new InvalidArgumentException('Planned delivery quantity cannot exceed packing lot output quantity.');
        }

        return DB::transaction(function () use ($user, $data, $packingLot, $contract, $acceptedQuantity, $plannedQuantity, $unitPrice): DeliveryNote {
            $grossRevenue = $acceptedQuantity * $unitPrice;

            $delivery = DeliveryNote::create([
                'farm_id' => $packingLot->farm_id,
                'packing_lot_id' => $packingLot->id,
                'supply_contract_id' => $contract?->id,
                'delivered_by_user_id' => $user->id,
                'code' => $data['code'] ?? $this->generateCode(),
                'customer_name' => $data['customer_name'] ?? $contract?->customer_name,
                'customer_type' => $data['customer_type'] ?? $contract?->customer_type,
                'delivered_at' => $data['delivered_at'],
                'planned_quantity' => $plannedQuantity,
                'accepted_quantity' => $acceptedQuantity,
                'returned_quantity' => 0,
                'net_quantity' => $acceptedQuantity,
                'unit' => $data['unit'] ?? $packingLot->unit,
                'unit_price' => $unitPrice,
                'gross_revenue' => $grossRevenue,
                'return_deduction' => 0,
                'side_channel_revenue' => (float) ($data['side_channel_revenue'] ?? 0),
                'net_revenue' => $grossRevenue + (float) ($data['side_channel_revenue'] ?? 0),
                'status' => 'accepted',
                'notes' => $data['notes'] ?? null,
                'metadata' => [
                    'price_snapshot' => [
                        'unit_price' => $unitPrice,
                        'unit' => $data['unit'] ?? $packingLot->unit,
                        'captured_at' => now()->toIso8601String(),
                    ],
                    'contract_snapshot' => $contract ? [
                        'customer_name' => $contract->customer_name,
                        'customer_type' => $contract->customer_type,
                        'frequency' => $contract->frequency,
                    ] : null,
                ],
            ]);

            DeliveryAcceptanceRecord::create([
                'delivery_note_id' => $delivery->id,
                'accepted_by_name' => $data['accepted_by_name'] ?? null,
                'accepted_at' => $data['accepted_at'] ?? $data['delivered_at'],
                'accepted_quantity' => $acceptedQuantity,
                'rejected_quantity' => max(0, $plannedQuantity - $acceptedQuantity),
                'evidence_photo_paths' => $data['evidence_photo_paths'] ?? [],
                'notes' => $data['acceptance_notes'] ?? null,
            ]);

            return $delivery->refresh();
        });
    }

    public function createReturn(User $user, array $data): ReturnRecord
    {
        $delivery = DeliveryNote::with('packingLot')->findOrFail($data['delivery_note_id']);
        $this->assertUserCanAccessFarm($user, (int) $delivery->farm_id);

        $quantity = (float) $data['quantity'];
        $newReturnedQuantity = (float) $delivery->returned_quantity + $quantity;

        if ($newReturnedQuantity > (float) $delivery->accepted_quantity) {
            throw new InvalidArgumentException('Returned quantity cannot exceed accepted delivery quantity.');
        }

        return DB::transaction(function () use ($user, $data, $delivery, $quantity, $newReturnedQuantity): ReturnRecord {
            $revenueDeduction = $quantity * (float) $delivery->unit_price;

            $return = ReturnRecord::create([
                'farm_id' => $delivery->farm_id,
                'delivery_note_id' => $delivery->id,
                'packing_lot_id' => $delivery->packing_lot_id,
                'recorded_by_user_id' => $user->id,
                'returned_at' => $data['returned_at'],
                'quantity' => $quantity,
                'unit' => $data['unit'] ?? $delivery->unit,
                'reason' => $data['reason'],
                'handling_action' => $data['handling_action'],
                'revenue_deduction' => $revenueDeduction,
                'evidence_photo_paths' => $data['evidence_photo_paths'] ?? [],
                'notes' => $data['notes'] ?? null,
                'metadata' => [
                    'packing_lot_code' => $delivery->packingLot?->code,
                    'unit_price_snapshot' => (float) $delivery->unit_price,
                ],
            ]);

            $returnDeduction = (float) $delivery->returnRecords()->sum('revenue_deduction');
            $sideChannelRevenue = (float) ($data['side_channel_revenue'] ?? $delivery->side_channel_revenue);
            $netQuantity = (float) $delivery->accepted_quantity - $newReturnedQuantity;
            $netRevenue = (float) $delivery->gross_revenue - $returnDeduction + $sideChannelRevenue;

            $delivery->update([
                'returned_quantity' => $newReturnedQuantity,
                'net_quantity' => $netQuantity,
                'return_deduction' => $returnDeduction,
                'side_channel_revenue' => $sideChannelRevenue,
                'net_revenue' => $netRevenue,
                'status' => $newReturnedQuantity >= (float) $delivery->accepted_quantity
                    ? 'returned'
                    : 'partially_returned',
            ]);

            return $return->refresh();
        });
    }

    public function revenueSnapshot(DeliveryNote $delivery): array
    {
        return [
            'delivery_note_id' => $delivery->id,
            'accepted_quantity' => (float) $delivery->accepted_quantity,
            'returned_quantity' => (float) $delivery->returned_quantity,
            'net_quantity' => (float) $delivery->net_quantity,
            'unit' => $delivery->unit,
            'unit_price' => (float) $delivery->unit_price,
            'gross_revenue' => (float) $delivery->gross_revenue,
            'return_deduction' => (float) $delivery->return_deduction,
            'side_channel_revenue' => (float) $delivery->side_channel_revenue,
            'net_revenue' => (float) $delivery->net_revenue,
            'price_snapshot' => $delivery->metadata['price_snapshot'] ?? null,
        ];
    }

    private function assertUserCanAccessFarm(User $user, int $farmId): void
    {
        if (!$user->isAdmin() && $user->farm_id !== $farmId) {
            throw new InvalidArgumentException('You do not have permission to manage delivery records for this farm.');
        }
    }

    private function assertPackingLotCanDeliver(PackingLot $packingLot): void
    {
        if (!in_array($packingLot->status, ['packed', 'published'], true)) {
            throw new InvalidArgumentException('Packing lot is not ready for delivery.');
        }
    }

    private function generateCode(): string
    {
        return 'DN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
