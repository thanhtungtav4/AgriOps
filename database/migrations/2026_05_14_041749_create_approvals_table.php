<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();

            // What is being approved
            $table->enum('resource_type', [
                'production_plan',
                'planting_batch',
                'chemical_usage',
                'pre_harvest_inspection',
                'harvest',
                'packing',
                'delivery',
                'post_season_review',
                'norm_adjustment',
                'cross_farm_allocation',
                'other',
            ]);
            $table->unsignedBigInteger('resource_id');

            // Approval context
            $table->foreignId('farm_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('approval_type')->nullable(); // e.g., 'plan_submission', 'chemical_use', 'harvest_release'
            $table->text('description')->nullable();

            // Status workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');

            // Requester info
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();

            // Approver info
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Rejection reason
            $table->text('rejected_reason')->nullable();

            // Attachments/evidence
            $table->json('attachments')->nullable(); // array of file paths/urls

            // Priority/urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Deadline for approval
            $table->timestamp('deadline')->nullable();

            // Reminder tracking
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();

            $table->timestamps();

            $table->index('resource_type');
            $table->index('resource_id');
            $table->index('farm_id');
            $table->index('status');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index(['resource_type', 'resource_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};