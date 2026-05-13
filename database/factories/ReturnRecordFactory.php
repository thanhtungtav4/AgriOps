<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\DeliveryNote;
use App\Models\PackingLot;
use App\Models\ReturnRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnRecord>
 */
class ReturnRecordFactory extends Factory
{
    protected $model = ReturnRecord::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(3, 1, 50);
        $price = fake()->randomFloat(2, 5000, 30000);

        return [
            'farm_id' => Farm::factory(),
            'delivery_note_id' => DeliveryNote::factory(),
            'packing_lot_id' => PackingLot::factory(),
            'recorded_by_user_id' => null,
            'returned_at' => now(),
            'quantity' => $qty,
            'unit' => 'kg',
            'reason' => fake()->randomElement(ReturnRecord::REASONS),
            'handling_action' => fake()->randomElement(ReturnRecord::HANDLING_ACTIONS),
            'revenue_deduction' => $qty * $price,
            'evidence_photo_paths' => null,
            'notes' => fake()->sentence(),
            'metadata' => null,
        ];
    }
}
