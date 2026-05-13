<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')->constrained()->onDelete('cascade');

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

            $table->foreignId('growth_stage_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('title');
            $table->string('task_type')->nullable();
            $table->string('status', 20)->default('planned');
            $table->string('priority', 20)->default('normal');

            $table->date('planned_start_date')->nullable();
            $table->date('planned_due_date')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('instructions')->nullable();
            $table->text('completion_note')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['assigned_user_id', 'status']);
            $table->index(['planned_due_date', 'status']);
            $table->index(['planting_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_tasks');
    }
};
