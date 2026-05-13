<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('actor_type')->nullable();
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('allocated_area_m2', 12, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index('event_type');
            $table->index('entity_type');
            $table->index('entity_id');
            $table->index('farm_id');
            $table->index('user_id');
            $table->index(['entity_type', 'entity_id']);
            $table->index(['farm_id', 'event_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
