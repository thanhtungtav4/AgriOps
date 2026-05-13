<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_batch_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planting_batch_id')
                ->constrained('planting_batches')
                ->onDelete('cascade');
            $table->foreignId('plot_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('bed_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
            $table->decimal('allocated_area_m2', 12, 2)->nullable();
            $table->string('notes')->nullable();
            $table->enum('status', [
                'allocated',
                'active',
                'completed',
                'cancelled',
            ])->default('allocated');

            $table->timestamps();

            $table->index('planting_batch_id');
            $table->index('plot_id');
            $table->index('status');
            $table->index(['planting_batch_id', 'status']);
            $table->unique(['planting_batch_id', 'plot_id', 'bed_id'], 'alloc_batch_plot_bed_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_batch_allocations');
    }
};
