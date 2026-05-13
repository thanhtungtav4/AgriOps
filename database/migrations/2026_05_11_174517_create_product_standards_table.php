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
        Schema::create('product_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('set null');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('specifications')->nullable();
            $table->decimal('allowed_defect_percent', 5, 2)->default(0);
            $table->string('packing_spec')->nullable();
            $table->enum('grade', ['A', 'B', 'C', 'reject'])->default('A');
            $table->timestamps();

            $table->index('crop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_standards');
    }
};
