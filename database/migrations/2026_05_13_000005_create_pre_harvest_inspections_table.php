<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_harvest_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('inspector_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status', 20)->default('submitted');
            $table->timestamp('inspected_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->json('checklist');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['planting_batch_id', 'status']);
            $table->index(['inspected_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_harvest_inspections');
    }
};
