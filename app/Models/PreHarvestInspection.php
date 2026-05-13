<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreHarvestInspection extends Model
{
    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'plot_id',
        'inspector_user_id',
        'approved_by_user_id',
        'status',
        'inspected_at',
        'approved_at',
        'rejected_at',
        'checklist',
        'notes',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'checklist' => 'array',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'submitted',
    ];

    public const STATUSES = ['submitted', 'approved', 'rejected'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function hasFailedCriteria(): bool
    {
        foreach ($this->checklist ?? [] as $item) {
            if (($item['passed'] ?? false) !== true) {
                return true;
            }
        }

        return false;
    }
}
