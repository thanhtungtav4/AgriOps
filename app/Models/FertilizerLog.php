<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizerLog extends Model
{
    use HasFactory;

    protected $table = 'fertilizer_logs';

    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'plot_id',
        'bed_id',
        'fertilized_at',
        'fertilizer_name',
        'fertilizer_type',
        'planned_quantity',
        'actual_quantity',
        'unit',
        'nitrogen_percent',
        'phosphorus_percent',
        'potassium_percent',
        'method',
        'cost',
        'weather_condition',
        'soil_moisture_before',
        'notes',
        'logged_by',
        'farming_log_id',
    ];

    protected $casts = [
        'fertilized_at' => 'date',
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'nitrogen_percent' => 'decimal:2',
        'phosphorus_percent' => 'decimal:2',
        'potassium_percent' => 'decimal:2',
        'cost' => 'decimal:2',
        'soil_moisture_before' => 'decimal:2',
    ];

    const METHODS = [
        'broadcast' => 'broadcast',
        'drip' => 'drip',
        'foliar' => 'foliar',
        'injection' => 'injection',
        'manual' => 'manual',
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
        return $query->whereBetween('fertilized_at', [$from, $to]);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('fertilizer_type', $type);
    }
}