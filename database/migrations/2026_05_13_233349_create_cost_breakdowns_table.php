<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();

            // Scope: which entity this breakdown is for
            $table->enum('breakdown_type', ['farm', 'production_plan', 'planting_batch', 'seasonal']);
            $table->foreignId('production_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('planting_batch_id')->nullable()->constrained()->cascadeOnDelete();

            // Period
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_label')->nullable(); // e.g., "Q1 2026", "Season 2025-2026"

            // Cost by category
            $table->decimal('total_seed_cost', 14, 2)->default(0);
            $table->decimal('total_fertilizer_cost', 14, 2)->default(0);
            $table->decimal('total_chemical_cost', 14, 2)->default(0);
            $table->decimal('total_water_cost', 14, 2)->default(0);
            $table->decimal('total_labor_cost', 14, 2)->default(0);
            $table->decimal('total_machinery_cost', 14, 2)->default(0);
            $table->decimal('total_land_rent_cost', 14, 2)->default(0);
            $table->decimal('total_other_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);

            // Yield metrics
            $table->decimal('total_yield_kg', 12, 3)->nullable();
            $table->decimal('cost_per_kg', 10, 4)->nullable();
            $table->decimal('total_revenue', 14, 2)->nullable();
            $table->decimal('gross_margin', 14, 2)->nullable();
            $table->decimal('gross_margin_percent', 8, 2)->nullable();

            // Metadata
            $table->json('breakdown_by_subcategory')->nullable();
            $table->json('cost_trends')->nullable(); // monthly cost trends
            $table->json('variances')->nullable(); // vs planned vs previous period
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('farm_id');
            $table->index('breakdown_type');
            $table->index('production_plan_id');
            $table->index('planting_batch_id');
            $table->index(['farm_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_breakdowns');
    }
};