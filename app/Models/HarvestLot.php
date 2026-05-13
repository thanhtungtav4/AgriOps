<?php

namespace App\Models;

use App\Enums\RejectReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarvestLot extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'pre_harvest_inspection_id',
        'work_task_id',
        'plot_id',
        'bed_id',
        'harvested_by_user_id',
        'code',
        'harvest_date',
        'raw_quantity',
        'unit',
        'grade_a_quantity',
        'grade_b_quantity',
        'grade_c_quantity',
        'reject_quantity',
        'reject_reasons',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'raw_quantity' => 'decimal:3',
        'grade_a_quantity' => 'decimal:3',
        'grade_b_quantity' => 'decimal:3',
        'grade_c_quantity' => 'decimal:3',
        'reject_quantity' => 'decimal:3',
        'reject_reasons' => 'array',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'unit' => 'kg',
        'status' => 'available',
    ];

    protected $appends = [
        'reject_reasons_summary',
    ];

    public const STATUSES = ['available', 'reserved', 'packed', 'cancelled'];
    public const VALID_REJECT_REASONS = RejectReason::VALUES;

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function preHarvestInspection(): BelongsTo
    {
        return $this->belongsTo(PreHarvestInspection::class, 'pre_harvest_inspection_id');
    }

    public function workTask(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function harvestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'harvested_by_user_id');
    }

    public function processingRecords(): HasMany
    {
        return $this->hasMany(ProcessingRecord::class);
    }

    public function packingSources(): HasMany
    {
        return $this->hasMany(PackingLotSource::class);
    }

    public function getRejectReasonsSummaryAttribute(): array
    {
        $reasons = $this->reject_reasons ?? [];

        return array_map(function ($qty, $reason) {
            $rejectReason = RejectReason::tryFrom($reason);

            return [
                'reason' => $reason,
                'label' => $rejectReason?->label() ?? $reason,
                'quantity' => (float) $qty,
            ];
        }, $reasons, array_keys($reasons));
    }
}
