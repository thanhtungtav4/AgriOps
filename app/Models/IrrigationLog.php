<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'plot_id',
        'bed_id',
        'irrigated_at',
        'planned_quantity',
        'actual_quantity',
        'method',
        'duration_minutes',
        'water_source',
        'ph',
        'temperature',
        'notes',
        'logged_by',
        'farming_log_id',
    ];

    protected $casts = [
        'irrigated_at' => 'date',
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'ph' => 'decimal:2',
        'temperature' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    const METHODS = [
        'drip' => 'drip',
        'sprinkler' => 'sprinkler',
        'manual' => 'manual',
        'flood' => 'flood',
        'other' => 'other',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function farmingLog(): BelongsTo
    {
        return $this->belongsTo(FarmingLog::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeForBatch($query, int $batchId)
    {
        return $query->where('planting_batch_id', $batchId);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereBetween('irrigated_at', [$from, $to]);
    }
}