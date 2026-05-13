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
        Schema::create('supply_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('customer_type', ['restaurant', 'wholesale', 'retail', 'export', 'other'])->default('other');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 14, 2);
            $table->enum('unit', ['kg', 'trái', 'bó', 'thùng']);
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly', 'seasonal'])->default('once');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('product_standard_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('crop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_contracts');
    }
};
