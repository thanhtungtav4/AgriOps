<?php

namespace Database\Factories;

use App\Models\PostSeasonReview;
use App\Models\ProductionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostSeasonReview>
 */
class PostSeasonReviewFactory extends Factory
{
    protected $model = PostSeasonReview::class;

    public function definition(): array
    {
        return [
            'production_plan_id' => ProductionPlan::factory(),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'rejected']),
            'actual_performance_summary' => fake()->paragraph(),
            'actual_total_cost' => fake()->randomFloat(2, 1000, 50000),
            'budget_variance' => fake()->randomFloat(2, -20, 20),
            'yield_analysis' => fake()->paragraph(),
            'quality_assessment' => fake()->paragraph(),
            'resource_utilization_review' => fake()->sentence(),
            'pest_disease_review' => fake()->sentence(),
            'weather_impact_analysis' => fake()->sentence(),
            'lessons_learned' => fake()->paragraph(),
            'recommendations' => fake()->paragraph(),
            'next_season_improvements' => fake()->paragraph(),
            'rejected_reason' => null,
            'submitted_by' => null,
            'submitted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'submitted',
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDay(),
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDay(),
            'approved_by' => User::factory(),
            'approved_at' => now(),
            'rejected_reason' => fake()->sentence(),
        ]);
    }
}