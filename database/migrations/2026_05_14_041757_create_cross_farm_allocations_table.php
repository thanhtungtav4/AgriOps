<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cross_farm_allocations', function (Blueprint $table) {
            $table->id();

            // Which production plan / delivery has shortfall
            $table->foreignId('source_farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('production_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('reason')->default('shortfall'); // shortfall, emergency, etc.

            // The supplemental source
            $table->foreignId('supplement_farm_id')->constrained('farms')->cascadeOnDelete();

            // What is being allocated
            $table->foreignId('planting_batch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('crop_id')->nullable()->constrained()->cascadeOnDelete();

            // Quantity info
            $table->decimal('requested_quantity', 14, 3)->default(0);
            $table->decimal('allocated_quantity', 14, 3)->default(0);
            $table->string('unit')->default('kg');

            // Packing info if applicable
            $table->string('packing_lot_code')->nullable();
            $table->string('qr_code')->nullable();

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('pending');

            // Notes
            $table->text('notes')->nullable();

            // Approval tracking
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index('source_farm_id');
            $table->index('supplement_farm_id');
            $table->index('production_plan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cross_farm_allocations');
    }
};