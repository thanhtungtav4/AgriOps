<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('harvest_lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at');
            $table->decimal('input_quantity', 12, 3);
            $table->decimal('output_quantity', 12, 3);
            $table->decimal('loss_quantity', 12, 3)->default(0);
            $table->decimal('loss_rate', 8, 4)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->string('status', 20)->default('completed');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'processed_at']);
            $table->index(['harvest_lot_id', 'processed_at']);
            $table->index(['status', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_records');
    }
};