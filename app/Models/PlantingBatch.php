<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PlantingBatch extends Model
{
    use HasFactory;
    protected $attributes = [
        'status' => 'planned',
    ];

    protected $fillable = [
        'farm_id',
        'crop_id',
        'variety_id',
        'production_plan_id',
        'code',
        'planned_quantity',
        'planned_unit',
        'planned_area_m2',
        'planned_start_date',
        'planned_harvest_date',
        'actual_quantity',
        'actual_area_m2',
        'actual_start_date',
        'actual_harvest_date',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_harvest_date' => 'date',
        'actual_start_date' => 'date',
        'actual_harvest_date' => 'date',
        'planned_quantity' => 'decimal:2',
        'planned_area_m2' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'actual_area_m2' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PlantingBatchAllocation::class);
    }

    public function plots()
    {
        return $this->hasManyThrough(Plot::class, PlantingBatchAllocation::class, 'planting_batch_id', 'id', 'id', 'plot_id');
    }

    public function workTasks(): HasMany
    {
        return $this->hasMany(WorkTask::class);
    }
}
