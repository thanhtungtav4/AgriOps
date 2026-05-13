<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'production_plan_id',
        'planting_batch_id',
        'recorded_by_user_id',
        'cost_category',
        'amount',
        'quantity',
        'unit',
        'occurred_at',
        'source_type',
        'source_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'decimal:3',
        'occurred_at' => 'date',
        'metadata' => 'array',
    ];

    public const CATEGORIES = [
        'seed',
        'fertilizer',
        'chemical_biological',
        'water',
        'labor',
        'land_rent',
        'machinery',
        'incident',
        'other',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
