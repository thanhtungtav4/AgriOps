<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropVariety extends Model
{
    use HasFactory;
    protected $fillable = [
        'crop_id',
        'name',
        'code',
        'supplier',
        'description',
        'avg_growth_days',
        'avg_yield_per_plant',
        'planting_density_per_m2',
        'disease_resistance',
        'suitable_season',
        'suitable_climate_zone',
        'care_requirements',
        'image_url',
        'status',
        'notes',
    ];

    protected $casts = [
        'avg_yield_per_plant' => 'decimal:4',
        'planting_density_per_m2' => 'decimal:2',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function harvestModels(): HasMany
    {
        return $this->hasMany(HarvestModel::class, 'variety_id');
    }

    public function lossProfiles(): HasMany
    {
        return $this->hasMany(LossProfile::class, 'variety_id');
    }

    public function laborNorms(): HasMany
    {
        return $this->hasMany(LaborNorm::class, 'variety_id');
    }
}
