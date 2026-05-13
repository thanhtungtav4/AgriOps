<?php

namespace Database\Factories;

use App\Models\Crop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Crop>
 */
class CropFactory extends Factory
{
    protected $model = Crop::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Rau muống', 'Xà lách', 'Cải thìa', 'Dưa leo', 'Cà chua', 'Ớt', 'Rau thơm']),
            'group' => fake()->randomElement(['leafy', 'fruit', 'root', 'fruit_tree']),
            'sale_unit' => fake()->randomElement(['kg', 'trái', 'bó', 'thùng']),
            'production_unit' => fake()->randomElement(['cây', 'm2', 'luống']),
            'can_harvest_multiple' => fake()->boolean(),
            'has_multiple_cycles' => fake()->boolean(),
            'avg_growth_days' => fake()->numberBetween(30, 120),
            'harvest_exploitation_days' => fake()->numberBetween(1, 30),
            'rest_days' => fake()->numberBetween(3, 14),
            'sale_price_per_unit' => fake()->randomFloat(2, 5000, 50000),
        ];
    }

    public function leafyGreen(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Xà lách', 'Cải thìa', 'Rau muống', 'Cải xanh']),
            'group' => 'leafy',
            'avg_growth_days' => fake()->numberBetween(25, 45),
        ]);
    }

    public function fruit(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Cà chua', 'Dưa leo', 'Ớt']),
            'group' => 'fruit',
            'avg_growth_days' => fake()->numberBetween(60, 120),
            'can_harvest_multiple' => true,
        ]);
    }
}
