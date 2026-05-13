<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizerNorm extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'growth_stage_id',
        'fertilizer_name',
        'amount',
        'unit',
        'application_day_range',
        'technical_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
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
