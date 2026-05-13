<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\GrowthStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GrowthStage>
 */
class GrowthStageFactory extends Factory
{
    protected $model = GrowthStage::class;

    public function definition(): array
    {
        return [
            'crop_id' => Crop::factory(),
            'variety_id' => null,
            'name' => fake()->randomElement(['Gieo hạt', 'Làm đất', 'Trồng cây', 'Chăm sóc', 'Thu hoạch']),
            'code' => 'STAGE-' . fake()->unique()->numberBetween(100, 999),
            'order' => fake()->numberBetween(1, 10),
            'duration_days' => fake()->numberBetween(3, 30),
            'description' => fake()->sentence(),
        ];
    }
}
