<?php

namespace App\Services;

use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PackingLotService
{
    public function create(User $user, array $data): PackingLot
    {
        $this->assertNotEmptySources($data['sources'] ?? []);
        $this->assertNoDuplicateSources($data['sources']);
        $this->assertUserCanOwnPackingLot($user, (int) $data['farm_id']);

        $validatedSources = array_map(function (array $source) use ($user, $data): array {
            $harvestLot = HarvestLot::findOrFail($source['harvest_lot_id']);

            $this->assertHarvestLotIsAvailable($harvestLot);
            $this->assertHarvestLotNotExceedsRawQuantity($harvestLot, $source['quantity']);
            $this->assertUserCanAccessHarvestLot($user, $harvestLot);

            return [
                'harvest_lot' => $harvestLot,
                'quantity' => $source['quantity'],
                'unit' => $source['unit'] ?? $data['unit'] ?? 'kg',
                'metadata' => $source['metadata'] ?? [],
            ];
        }, $data['sources']);

        return DB::transaction(function () use ($user, $data, $validatedSources): PackingLot {
            $packingLot = PackingLot::create([
                'farm_id' => $data['farm_id'],
                'created_by_user_id' => $user->id,
                'code' => $data['code'] ?? $this->generateCode($data),
                'packed_at' => $data['packed_at'],
                'status' => 'packed',
                'total_input_quantity' => $this->calculateTotalInputQuantity($data['sources']),
                'total_output_quantity' => $data['total_output_quantity'],
                'unit' => $data['unit'] ?? 'kg',
                'qr_code' => $data['qr_code'] ?? $this->generateQrCode(),
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            foreach ($validatedSources as $source) {
                $harvestLot = $source['harvest_lot'];

                PackingLotSource::create([
                    'packing_lot_id' => $packingLot->id,
                    'harvest_lot_id' => $harvestLot->id,
                    'farm_id' => $harvestLot->farm_id,
                    'planting_batch_id' => $harvestLot->planting_batch_id,
                    'quantity' => $source['quantity'],
                    'unit' => $source['unit'],
                    'metadata' => $source['metadata'],
                ]);

                $harvestLot->update(['status' => 'packed']);
            }

            return $packingLot;
        });
    }

    private function assertNotEmptySources(array $sources): void
    {
        if (empty($sources)) {
            throw new InvalidArgumentException('Packing lot must have at least one source.');
        }
    }

    private function assertNoDuplicateSources(array $sources): void
    {
        $harvestLotIds = array_column($sources, 'harvest_lot_id');
        $uniqueIds = array_unique($harvestLotIds);

        if (count($harvestLotIds) !== count($uniqueIds)) {
            throw new InvalidArgumentException('Duplicate harvest lots in sources are not allowed.');
        }
    }

    private function assertHarvestLotIsAvailable(HarvestLot $harvestLot): void
    {
        if ($harvestLot->status !== 'available') {
            throw new InvalidArgumentException(
                "Harvest lot {$harvestLot->code} is not available for packing. Current status: {$harvestLot->status}."
            );
        }
    }

    private function assertHarvestLotNotExceedsRawQuantity(HarvestLot $harvestLot, float $quantity): void
    {
        if ($quantity > (float) $harvestLot->raw_quantity) {
            throw new InvalidArgumentException(
                "Source quantity ({$quantity}) cannot exceed harvest lot raw quantity ({$harvestLot->raw_quantity})."
            );
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Source quantity must be greater than zero.');
        }
    }

    private function assertUserCanAccessHarvestLot(User $user, HarvestLot $harvestLot): void
    {
        if (!$user->isAdmin() && $harvestLot->farm_id !== $user->farm_id) {
            throw new InvalidArgumentException('You do not have permission to pack this harvest lot.');
        }
    }

    private function assertUserCanOwnPackingLot(User $user, int $farmId): void
    {
        if (!$user->isAdmin() && $farmId !== $user->farm_id) {
            throw new InvalidArgumentException('You do not have permission to create packing lots for this farm.');
        }
    }

    private function calculateTotalInputQuantity(array $sources): float
    {
        return array_sum(array_column($sources, 'quantity'));
    }

    private function generateCode(array $data): string
    {
        $prefix = 'PL';
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$date}-{$random}";
    }

    private function generateQrCode(): string
    {
        return 'qr_' . Str::random(24);
    }
}
