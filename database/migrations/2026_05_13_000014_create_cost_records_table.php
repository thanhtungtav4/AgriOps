<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_plan_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cost_category', 40);
            $table->decimal('amount', 14, 2);
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit', 20)->nullable();
            $table->date('occurred_at');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'cost_category']);
            $table->index(['production_plan_id', 'cost_category']);
            $table->index(['planting_batch_id', 'cost_category']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_records');
    }
};
