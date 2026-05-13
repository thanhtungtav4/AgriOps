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
        Schema::create('harvest_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->enum('harvest_type', ['single', 'multiple'])->default('single');
            $table->decimal('avg_yield_per_plant', 12, 4);
            $table->decimal('min_yield_per_plant', 12, 4)->nullable();
            $table->decimal('max_yield_per_plant', 12, 4)->nullable();
            $table->decimal('planting_density_per_m2', 10, 2);
            $table->decimal('survival_rate', 5, 2)->default(100);
            $table->decimal('grade_a_percent', 5, 2)->default(100);
            $table->decimal('grade_b_percent', 5, 2)->default(0);
            $table->decimal('grade_c_percent', 5, 2)->default(0);
            $table->decimal('reject_percent', 5, 2)->default(0);
            $table->unsignedSmallInteger('days_to_first_harvest')->default(0);
            $table->unsignedSmallInteger('harvest_duration_days')->default(0);
            $table->timestamps();

            $table->index(['crop_id', 'variety_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_models');
    }
};
