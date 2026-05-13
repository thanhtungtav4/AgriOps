<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('farming_log_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('incident_type', 50);
            $table->string('severity', 20)->default('medium');
            $table->string('status', 20)->default('open');
            $table->timestamp('detected_at');
            $table->text('description')->nullable();
            $table->text('treatment_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['planting_batch_id', 'status']);
            $table->index(['plot_id', 'status']);
            $table->index(['detected_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
