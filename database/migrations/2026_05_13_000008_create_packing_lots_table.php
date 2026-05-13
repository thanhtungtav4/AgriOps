<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->unique();
            $table->timestamp('packed_at');
            $table->string('status', 20)->default('draft');
            $table->decimal('total_input_quantity', 12, 3)->default(0);
            $table->decimal('total_output_quantity', 12, 3)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->string('qr_code')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['packed_at', 'status']);
            $table->index(['qr_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_lots');
    }
};