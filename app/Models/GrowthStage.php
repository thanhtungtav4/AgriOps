<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowthStage extends Model
{
    use HasFactory;
    protected $fillable = [
        'crop_id',
        'variety_id',
        'name',
        'code',
        'order',
        'duration_days',
        'description',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }

    public function irrigationNorms(): HasMany
    {
        return $this->hasMany(IrrigationNorm::class);
    }

    public function fertilizerNorms(): HasMany
    {
        return $this->hasMany(FertilizerNorm::class);
    }
}
