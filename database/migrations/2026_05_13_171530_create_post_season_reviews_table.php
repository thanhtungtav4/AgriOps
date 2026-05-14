<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_season_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('actual_performance_summary')->nullable();
            $table->decimal('actual_total_cost', 14, 2)->nullable();
            $table->decimal('budget_variance', 14, 2)->nullable();
            $table->text('yield_analysis')->nullable();
            $table->text('quality_assessment')->nullable();
            $table->text('resource_utilization_review')->nullable();
            $table->text('pest_disease_review')->nullable();
            $table->text('weather_impact_analysis')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('next_season_improvements')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_season_reviews');
    }
};
