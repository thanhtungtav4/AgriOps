<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingBatchAllocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'planting_batch_id',
        'plot_id',
        'bed_id',
        'allocated_area_m2',
        'notes',
        'status',
    ];

    protected $casts = [
        'allocated_area_m2' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function workTasks(): HasMany
    {
        return $this->hasMany(WorkTask::class, 'planting_batch_allocation_id');
    }
}
