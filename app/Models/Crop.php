<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crop extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'group',
        'sale_unit',
        'production_unit',
        'can_harvest_multiple',
        'has_multiple_cycles',
        'avg_growth_days',
        'harvest_exploitation_days',
        'rest_days',
        'sale_price_per_unit',
    ];

    protected $casts = [
        'can_harvest_multiple' => 'boolean',
        'has_multiple_cycles' => 'boolean',
        'sale_price_per_unit' => 'decimal:2',
    ];

    public function varieties(): HasMany
    {
        return $this->hasMany(CropVariety::class);
    }

    public function standards(): HasMany
    {
        return $this->hasMany(ProductStandard::class);
    }

    public function growthStages(): HasMany
    {
        return $this->hasMany(GrowthStage::class);
    }

    public function harvestModels(): HasMany
    {
        return $this->hasMany(HarvestModel::class);
    }

    public function lossProfiles(): HasMany
    {
        return $this->hasMany(LossProfile::class);
    }

    public function laborNorms(): HasMany
    {
        return $this->hasMany(LaborNorm::class);
    }

    public function irrigationNorms(): HasMany
    {
        return $this->hasMany(IrrigationNorm::class);
    }

    public function fertilizerNorms(): HasMany
    {
        return $this->hasMany(FertilizerNorm::class);
    }

    public function priceTables(): HasMany
    {
        return $this->hasMany(PriceTable::class);
    }
}
