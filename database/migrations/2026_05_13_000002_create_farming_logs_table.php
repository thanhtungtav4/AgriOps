<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farming_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('work_task_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('planting_batch_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('planting_batch_allocation_id')
                ->nullable()
                ->constrained('planting_batch_allocations')
                ->onDelete('set null');

            $table->foreignId('plot_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('bed_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('reported_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamp('logged_at');
            $table->string('status', 20)->default('submitted');

            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('photo_paths')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['farm_id', 'logged_at']);
            $table->index(['work_task_id', 'logged_at']);
            $table->index(['reported_by_user_id', 'logged_at']);
            $table->index(['planting_batch_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farming_logs');
    }
};