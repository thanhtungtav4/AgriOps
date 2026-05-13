<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationNorm extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'growth_stage_id',
        'frequency',
        'water_amount',
        'unit',
        'timing',
        'requires_actual_log',
        'notes',
    ];

    protected $casts = [
        'water_amount' => 'decimal:4',
        'requires_actual_log' => 'boolean',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }

    public function growthStage(): BelongsTo
    {
        return $this->belongsTo(GrowthStage::class);
    }
}
