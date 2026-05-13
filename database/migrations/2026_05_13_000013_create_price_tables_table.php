<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('cascade');
            $table->string('unit', 20)->default('kg');
            $table->decimal('grade_a_price', 14, 2);
            $table->decimal('grade_b_price', 14, 2)->default(0);
            $table->decimal('grade_c_price', 14, 2)->default(0);
            $table->decimal('side_channel_price', 14, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'crop_id', 'variety_id', 'unit']);
            $table->index(['effective_from', 'effective_until', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tables');
    }
};
