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
        Schema::create('fertilizer_norms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->foreignId('growth_stage_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('fertilizer_name');
            $table->decimal('amount', 12, 4)->default(0);
            $table->string('unit')->default('kg');
            $table->string('application_day_range')->nullable();
            $table->text('technical_notes')->nullable();
            $table->timestamps();

            $table->index(['crop_id', 'variety_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fertilizer_norms');
    }
};
