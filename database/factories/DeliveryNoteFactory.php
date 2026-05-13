<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\PackingLot;
use App\Models\DeliveryNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryNote>
 */
class DeliveryNoteFactory extends Factory
{
    protected $model = DeliveryNote::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(3, 10, 500);
        $price = fake()->randomFloat(2, 5000, 50000);

        return [
            'farm_id' => Farm::factory(),
            'packing_lot_id' => PackingLot::factory(),
            'supply_contract_id' => null,
            'delivered_by_user_id' => null,
            'code' => 'DEL-' . fake()->unique()->numberBetween(10000, 99999),
            'customer_name' => fake()->company(),
            'customer_type' => fake()->randomElement(['wholesaler', 'retailer', 'restaurant', 'market']),
            'delivered_at' => now(),
            'planned_quantity' => $qty,
            'accepted_quantity' => $qty,
            'returned_quantity' => 0,
            'net_quantity' => $qty,
            'unit' => 'kg',
            'unit_price' => $price,
            'gross_revenue' => $qty * $price,
            'return_deduction' => 0,
            'side_channel_revenue' => 0,
            'net_revenue' => $qty * $price,
            'status' => 'delivered',
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    public function partiallyReturned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partially_returned',
            'returned_quantity' => fake()->randomFloat(3, 1, 10),
        ]);
    }
}
