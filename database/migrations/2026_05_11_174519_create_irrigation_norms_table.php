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
        Schema::create('irrigation_norms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->foreignId('growth_stage_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('frequency')->nullable();
            $table->decimal('water_amount', 12, 4)->default(0);
            $table->enum('unit', ['liters_per_m2_per_day', 'liters_per_plant_per_day', 'liters_per_bed_per_day'])->default('liters_per_m2_per_day');
            $table->string('timing')->nullable();
            $table->boolean('requires_actual_log')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['crop_id', 'variety_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigation_norms');
    }
};
