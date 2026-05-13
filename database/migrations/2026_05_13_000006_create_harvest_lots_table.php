<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('pre_harvest_inspection_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('work_task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('harvested_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->nullable();
            $table->date('harvest_date');
            $table->decimal('raw_quantity', 14, 3);
            $table->string('unit', 20)->default('kg');
            $table->decimal('grade_a_quantity', 14, 3)->default(0);
            $table->decimal('grade_b_quantity', 14, 3)->default(0);
            $table->decimal('grade_c_quantity', 14, 3)->default(0);
            $table->decimal('reject_quantity', 14, 3)->default(0);
            $table->json('reject_reasons')->nullable();
            $table->string('status', 20)->default('available');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'harvest_date']);
            $table->index(['planting_batch_id', 'harvest_date']);
            $table->index(['status', 'harvest_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_lots');
    }
};
