<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\User;
use App\Models\PackingLot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PackingLot>
 */
class PackingLotFactory extends Factory
{
    protected $model = PackingLot::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'created_by_user_id' => User::factory(),
            'code' => 'PACK-' . fake()->unique()->numberBetween(10000, 99999),
            'packed_at' => now(),
            'status' => 'draft',
            'total_input_quantity' => fake()->randomFloat(3, 50, 500),
            'total_output_quantity' => fake()->randomFloat(3, 45, 480),
            'unit' => 'kg',
            'qr_code' => 'QR-' . Str::uuid(),
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function packed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'packed',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
