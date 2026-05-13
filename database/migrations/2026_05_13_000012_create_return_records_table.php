<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('packing_lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('returned_at');
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20)->default('kg');
            $table->string('reason', 50);
            $table->string('handling_action', 50);
            $table->decimal('revenue_deduction', 14, 2)->default(0);
            $table->json('evidence_photo_paths')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'reason']);
            $table->index('delivery_note_id');
            $table->index('packing_lot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_records');
    }
};
