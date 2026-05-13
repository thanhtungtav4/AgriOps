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
        Schema::create('crop_varieties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();
            $table->integer('avg_growth_days')->default(0);
            $table->decimal('avg_yield_per_plant', 12, 4)->nullable();
            $table->decimal('planting_density_per_m2', 10, 2)->nullable();
            $table->string('disease_resistance')->nullable();
            $table->string('suitable_season')->nullable();
            $table->string('suitable_climate_zone')->nullable();
            $table->text('care_requirements')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['crop_id', 'code']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_varieties');
    }
};
