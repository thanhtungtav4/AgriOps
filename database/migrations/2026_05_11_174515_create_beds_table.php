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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plot_id')->nullable()->index();
            $table->string('code');
            $table->decimal('length_m', 8, 2)->default(0);
            $table->decimal('width_m', 8, 2)->default(0);
            $table->decimal('area_m2', 10, 2)->default(0);
            $table->integer('expected_plants')->default(0);
            $table->enum('status', [
                'available',
                'preparing',
                'planting',
                'growing',
                'harvesting',
                'rest'
            ])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['plot_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};