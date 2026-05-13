<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChemicalUsage extends Model
{
    protected $fillable = [
        'farm_id',
        'incident_id',
        'planting_batch_id',
        'work_task_id',
        'plot_id',
        'bed_id',
        'applied_by_user_id',
        'product_name',
        'product_type',
        'active_ingredient',
        'dosage_value',
        'dosage_unit',
        'quantity_value',
        'quantity_unit',
        'cost_amount',
        'isolation_days',
        'applied_at',
        'isolation_ends_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'dosage_value' => 'decimal:3',
        'quantity_value' => 'decimal:3',
        'cost_amount' => 'decimal:2',
        'isolation_days' => 'integer',
        'applied_at' => 'datetime',
        'isolation_ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'product_type' => 'chemical',
        'isolation_days' => 0,
    ];

    public const PRODUCT_TYPES = ['chemical', 'biological'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function workTask(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }
}
