<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_type',
        'resource_id',
        'farm_id',
        'approval_type',
        'description',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_reason',
        'attachments',
        'priority',
        'deadline',
        'reminder_count',
        'last_reminder_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'deadline' => 'datetime',
        'last_reminder_at' => 'datetime',
        'attachments' => 'array',
        'reminder_count' => 'integer',
    ];

    const STATUSES = [
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'cancelled' => 'cancelled',
    ];

    const PRIORITIES = [
        'low' => 'low',
        'normal' => 'normal',
        'high' => 'high',
        'urgent' => 'urgent',
    ];

    const RESOURCE_TYPES = [
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
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUSES['pending']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUSES['approved']);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUSES['rejected']);
    }

    public function scopeForResource($query, string $type, int $resourceId)
    {
        return $query->where('resource_type', $type)->where('resource_id', $resourceId);
    }

    public function scopeForFarm($query, int $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent')->where('status', self::STATUSES['pending']);
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUSES['pending'];
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUSES['approved'];
    }

    public function approve(User $approver, ?string $notes = null): self
    {
        $this->update([
            'status' => self::STATUSES['approved'],
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /** @deprecated Use markRejected() to avoid collision with Collection::reject() */
    public function reject(User $approver, string $reason): self
    {
        return $this->markRejected($approver, $reason);
    }

    public function markRejected(User $approver, string $reason): self
    {
        $this->update([
            'status' => self::STATUSES['rejected'],
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejected_reason' => $reason,
        ]);

        return $this;
    }
}
