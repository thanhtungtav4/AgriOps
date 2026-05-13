<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_acceptance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->onDelete('cascade');
            $table->string('accepted_by_name')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->decimal('accepted_quantity', 12, 3);
            $table->decimal('rejected_quantity', 12, 3)->default(0);
            $table->json('evidence_photo_paths')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('delivery_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_acceptance_records');
    }
};
