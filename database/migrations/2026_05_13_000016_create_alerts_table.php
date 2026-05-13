<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('recipient_role', 40)->nullable();
            $table->string('alert_type', 60);
            $table->string('severity', 20)->default('warning');
            $table->string('title');
            $table->text('message');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('context')->nullable();
            $table->json('notification_payload')->nullable();
            $table->string('status', 20)->default('unread');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['alert_type', 'source_type', 'source_id', 'recipient_role'], 'alerts_unique_role_source');
            $table->index(['farm_id', 'status']);
            $table->index(['recipient_user_id', 'status']);
            $table->index(['recipient_role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
