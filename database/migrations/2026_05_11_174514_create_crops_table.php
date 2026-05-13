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
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('group', ['leafy', 'fruit', 'root', 'fruit_tree'])->default('leafy');
            $table->enum('sale_unit', ['kg', 'trái', 'bó', 'thùng'])->default('kg');
            $table->enum('production_unit', ['cây', 'm2', 'luống'])->default('cây');
            $table->boolean('can_harvest_multiple')->default(false);
            $table->boolean('has_multiple_cycles')->default(false);
            $table->integer('avg_growth_days')->default(0);
            $table->integer('harvest_exploitation_days')->default(0);
            $table->integer('rest_days')->default(0);
            $table->decimal('sale_price_per_unit', 14, 2)->nullable();
            $table->timestamps();

            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
