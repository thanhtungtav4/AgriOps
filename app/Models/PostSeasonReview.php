<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostSeasonReview extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
    ];

    protected $fillable = [
        'production_plan_id',
        'status',
        'actual_performance_summary',
        'actual_total_cost',
        'budget_variance',
        'yield_analysis',
        'quality_assessment',
        'resource_utilization_review',
        'pest_disease_review',
        'weather_impact_analysis',
        'lessons_learned',
        'recommendations',
        'next_season_improvements',
        'rejected_reason',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'actual_total_cost' => 'decimal:2',
        'budget_variance' => 'decimal:2',
    ];

    const STATUSES = [
        'draft' => 'draft',
        'submitted' => 'submitted',
        'approved' => 'approved',
        'rejected' => 'rejected',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUSES['draft']);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUSES['submitted']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUSES['approved']);
    }
}