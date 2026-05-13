<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_lot_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('harvest_lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20)->default('kg');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['packing_lot_id', 'harvest_lot_id']);
            $table->index(['farm_id', 'harvest_lot_id']);
            $table->index(['packing_lot_id', 'harvest_lot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_lot_sources');
    }
};