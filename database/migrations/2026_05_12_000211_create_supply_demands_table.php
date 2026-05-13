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
        Schema::create('supply_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_contract_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 14, 2);
            $table->enum('unit', ['kg', 'trái', 'bó', 'thùng']);
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly', 'seasonal'])->default('once');
            $table->date('target_date');
            $table->enum('status', ['pending', 'planned', 'fulfilled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('supply_contract_id');
            $table->index('crop_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_demands');
    }
};
