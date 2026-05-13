<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chemical_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('incident_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('planting_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('work_task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('product_name');
            $table->string('product_type', 30)->default('chemical');
            $table->string('active_ingredient')->nullable();
            $table->decimal('dosage_value', 12, 3)->nullable();
            $table->string('dosage_unit', 30)->nullable();
            $table->decimal('quantity_value', 12, 3)->nullable();
            $table->string('quantity_unit', 30)->nullable();
            $table->decimal('cost_amount', 14, 2)->nullable();
            $table->unsignedInteger('isolation_days')->default(0);
            $table->timestamp('applied_at');
            $table->timestamp('isolation_ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'applied_at']);
            $table->index(['incident_id', 'applied_at']);
            $table->index(['planting_batch_id', 'isolation_ends_at']);
            $table->index(['plot_id', 'isolation_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chemical_usages');
    }
};
