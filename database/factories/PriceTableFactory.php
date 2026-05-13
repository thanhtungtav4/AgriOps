<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\PriceTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceTable>
 */
class PriceTableFactory extends Factory
{
    protected $model = PriceTable::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'crop_id' => Crop::factory(),
            'variety_id' => CropVariety::factory(),
            'unit' => 'kg',
            'grade_a_price' => fake()->randomFloat(2, 15000, 50000),
            'grade_b_price' => fake()->randomFloat(2, 10000, 30000),
            'grade_c_price' => fake()->randomFloat(2, 5000, 15000),
            'side_channel_price' => fake()->randomFloat(2, 3000, 10000),
            'effective_from' => now()->subDays(fake()->numberBetween(0, 30)),
            'effective_until' => now()->addDays(fake()->numberBetween(30, 180)),
            'status' => 'active',
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'effective_until' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
