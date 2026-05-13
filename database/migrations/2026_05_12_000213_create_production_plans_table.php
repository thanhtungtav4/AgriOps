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
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_contract_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('supply_demand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->foreignId('variety_id')->nullable()->constrained('crop_varieties')->onDelete('set null');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 14, 2);
            $table->enum('unit', ['kg', 'trái', 'bó', 'thùng']);
            $table->date('target_delivery_date');
            $table->decimal('estimated_cost', 14, 2)->nullable();
            $table->decimal('estimated_revenue', 14, 2)->nullable();
            $table->decimal('margin_percent', 8, 2)->nullable();
            $table->enum('status', ['draft', 'planning', 'approved', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('crop_id');
            $table->index('farm_id');
            $table->index('supply_contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
