<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('record_type', ['test_result', 'amendment', 'reading'])->default('test_result');
            $table->date('recorded_at');

            // Chemical properties
            $table->decimal('ph', 5, 2)->nullable();
            $table->decimal('nitrogen', 8, 2)->nullable();       // N (ppm)
            $table->decimal('phosphorus', 8, 2)->nullable();     // P (ppm)
            $table->decimal('potassium', 8, 2)->nullable();     // K (ppm)
            $table->decimal('organic_matter', 5, 2)->nullable(); // %
            $table->decimal('moisture', 5, 2)->nullable();       // %

            // Micronutrients
            $table->decimal('zinc', 8, 2)->nullable();
            $table->decimal('iron', 8, 2)->nullable();
            $table->decimal('manganese', 8, 2)->nullable();
            $table->decimal('copper', 8, 2)->nullable();
            $table->decimal('boron', 8, 2)->nullable();

            // Additional
            $table->text('amendment_applied')->nullable();  // fertilizer, lime, compost, etc.
            $table->decimal('amendment_quantity', 10, 2)->nullable();
            $table->string('amendment_unit')->nullable();
            $table->enum('source', ['lab', 'manual'])->default('manual');
            $table->string('lab_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('plot_id');
            $table->index('bed_id');
            $table->index('record_type');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_histories');
    }
};