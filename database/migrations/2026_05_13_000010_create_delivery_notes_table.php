<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('packing_lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('supply_contract_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('delivered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->string('customer_type')->nullable();
            $table->timestamp('delivered_at');
            $table->decimal('planned_quantity', 12, 3)->default(0);
            $table->decimal('accepted_quantity', 12, 3)->default(0);
            $table->decimal('returned_quantity', 12, 3)->default(0);
            $table->decimal('net_quantity', 12, 3)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('gross_revenue', 14, 2)->default(0);
            $table->decimal('return_deduction', 14, 2)->default(0);
            $table->decimal('side_channel_revenue', 14, 2)->default(0);
            $table->decimal('net_revenue', 14, 2)->default(0);
            $table->string('status', 30)->default('delivered');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['packing_lot_id', 'status']);
            $table->index('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
