<?php

namespace Database\Factories;

use App\Models\ChemicalProduct;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChemicalProduct>
 */
class ChemicalProductFactory extends Factory
{
    protected $model = ChemicalProduct::class;

    public function definition(): array
    {
        return [
            'farm_id' => null,
            'name' => fake()->words(3, true) . ' ' . fake()->randomNumber(3),
            'active_ingredient' => fake()->words(2, true),
            'type' => fake()->randomElement(['pesticide', 'herbicide', 'fungicide', 'fertilizer', 'biological']),
            'formulation' => fake()->randomElement(['bột', 'lỏng', 'hạt', 'viên']),
            'registration_number' => 'VN-' . fake()->unique()->randomNumber(6),
            'manufacturer' => fake()->company(),
            'supplier' => fake()->company(),
            'unit' => fake()->randomElement(['kg', 'lít', 'chai', 'gói']),
            'stock_quantity' => fake()->randomFloat(3, 10, 500),
            'min_stock_level' => fake()->randomFloat(3, 5, 50),
            'price_per_unit' => fake()->randomFloat(2, 50, 500),
            'usage_instructions' => fake()->sentence(),
            'safety_instructions' => fake()->sentence(),
            'storage_conditions' => 'Nơi khô ráo, tránh ánh nắng trực tiếp',
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => 2,
            'min_stock_level' => 10,
        ]);
    }

    public function forFarm(Farm $farm): static
    {
        return $this->state(fn(array $attributes) => [
            'farm_id' => $farm->id,
        ]);
    }
}