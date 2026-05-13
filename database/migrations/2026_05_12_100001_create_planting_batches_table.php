<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('set null');
            $table->foreignId('production_plan_id')->nullable()->constrained()->onDelete('set null');

            $table->string('code')->nullable();
            $table->decimal('planned_quantity', 14, 2)->nullable();
            $table->string('planned_unit', 20)->nullable();
            $table->decimal('planned_area_m2', 12, 2)->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_harvest_date')->nullable();

            $table->decimal('actual_quantity', 14, 2)->nullable();
            $table->decimal('actual_area_m2', 12, 2)->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_harvest_date')->nullable();

            $table->enum('status', [
                'planned',
                'planned_kh',
                'approved',
                'soil_prep',
                'planting',
                'growing',
                'flowering',
                'fruiting',
                'harvesting',
                'completed',
                'cancelled',
            ])->default('planned');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('farm_id');
            $table->index('crop_id');
            $table->index('status');
            $table->index(['farm_id', 'status']);
            $table->index(['farm_id', 'status', 'planned_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_batches');
    }
};
