<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chemical_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');                           // Tên thương mại
            $table->string('active_ingredient');              // Hoạt chất
            $table->enum('type', ['pesticide', 'herbicide', 'fungicide', 'fertilizer', 'biological', 'other']);
            $table->string('formulation')->nullable();        // Dạng (bột, lỏng, hạt)
            $table->string('registration_number')->nullable(); // Số đăng ký
            $table->string('manufacturer')->nullable();
            $table->string('supplier')->nullable();
            $table->string('unit');                            // kg, lít, chai
            $table->decimal('stock_quantity', 12, 3)->default(0);
            $table->decimal('min_stock_level', 12, 3)->default(0);
            $table->decimal('price_per_unit', 12, 2)->default(0);
            $table->text('usage_instructions')->nullable();
            $table->text('safety_instructions')->nullable();
            $table->text('storage_conditions')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('farm_id');
            $table->index('type');
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chemical_products');
    }
};