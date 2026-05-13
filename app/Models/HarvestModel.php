<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestModel extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'harvest_type',
        'avg_yield_per_plant',
        'min_yield_per_plant',
        'max_yield_per_plant',
        'planting_density_per_m2',
        'survival_rate',
        'grade_a_percent',
        'grade_b_percent',
        'grade_c_percent',
        'reject_percent',
        'days_to_first_harvest',
        'harvest_duration_days',
    ];

    protected $casts = [
        'avg_yield_per_plant' => 'decimal:4',
        'min_yield_per_plant' => 'decimal:4',
        'max_yield_per_plant' => 'decimal:4',
        'planting_density_per_m2' => 'decimal:2',
        'survival_rate' => 'decimal:2',
        'grade_a_percent' => 'decimal:2',
        'grade_b_percent' => 'decimal:2',
        'grade_c_percent' => 'decimal:2',
        'reject_percent' => 'decimal:2',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }
}
