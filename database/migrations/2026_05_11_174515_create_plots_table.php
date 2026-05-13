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
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->decimal('area_m2', 12, 2)->default(0);
            $table->string('soil_type')->nullable();
            $table->string('water_source')->nullable();
            $table->enum('status', [
                'available',
                'preparing',
                'planting',
                'harvesting',
                'rest',
                'restoring',
                'suspended'
            ])->default('available');
            $table->unsignedBigInteger('current_crop_id')->nullable();
            $table->unsignedBigInteger('current_batch_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'code']);
            $table->index('status');
            $table->index('current_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};