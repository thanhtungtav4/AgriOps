<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('planting_batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->cascadeOnDelete();

            // When irrigation happened
            $table->date('irrigated_at');

            // Planned vs actual
            $table->decimal('planned_quantity', 10, 2)->nullable(); // liters planned
            $table->decimal('actual_quantity', 10, 2)->nullable();   // liters actual

            // Method
            $table->enum('method', ['drip', 'sprinkler', 'manual', 'flood', 'other'])->default('manual');

            // Duration
            $table->integer('duration_minutes')->nullable();

            // Water source
            $table->string('water_source')->nullable(); // well, river, tap, etc.

            // Quality
            $table->decimal('ph', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable(); // celsius

            // Notes
            $table->text('notes')->nullable();

            // Who logged
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();

            // Linked farming log if any
            $table->foreignId('farming_log_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->index('farm_id');
            $table->index('planting_batch_id');
            $table->index('irrigated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_logs');
    }
};