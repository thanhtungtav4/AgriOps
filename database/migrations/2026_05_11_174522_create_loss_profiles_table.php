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
        Schema::create('loss_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->decimal('harvest_loss_percent', 5, 2)->default(0);
            $table->decimal('processing_loss_percent', 5, 2)->default(0);
            $table->decimal('packing_loss_percent', 5, 2)->default(0);
            $table->decimal('non_grade_a_percent', 5, 2)->default(0);
            $table->decimal('reject_percent', 5, 2)->default(0);
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
        Schema::dropIfExists('loss_profiles');
    }
};
