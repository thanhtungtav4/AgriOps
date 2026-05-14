<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fertilizer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('planting_batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->cascadeOnDelete();

            // When fertilization happened
            $table->date('fertilized_at');

            // Product used
            $table->string('fertilizer_name')->nullable();
            $table->string('fertilizer_type')->nullable(); // organic, chemical, bio, etc.

            // Quantity
            $table->decimal('planned_quantity', 10, 2)->nullable();
            $table->decimal('actual_quantity', 10, 2)->nullable();
            $table->string('unit')->default('kg');

            // NPK content if known
            $table->decimal('nitrogen_percent', 5, 2)->nullable();
            $table->decimal('phosphorus_percent', 5, 2)->nullable();
            $table->decimal('potassium_percent', 5, 2)->nullable();

            // Application method
            $table->enum('method', ['broadcast', 'drip', 'foliar', 'injection', 'manual', 'other'])->default('manual');

            // Cost
            $table->decimal('cost', 14, 2)->nullable();

            // Weather conditions
            $table->string('weather_condition')->nullable(); // sunny, cloudy, rainy, etc.
            $table->decimal('soil_moisture_before', 5, 2)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Who logged
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();

            // Linked farming log if any
            $table->foreignId('farming_log_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->index('farm_id');
            $table->index('planting_batch_id');
            $table->index('fertilized_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilizer_logs');
    }
};