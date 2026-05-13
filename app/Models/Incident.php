<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'work_task_id',
        'farming_log_id',
        'plot_id',
        'bed_id',
        'reported_by_user_id',
        'incident_type',
        'severity',
        'status',
        'detected_at',
        'description',
        'treatment_note',
        'metadata',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'open',
    ];

    public const TYPES = ['pest', 'disease', 'weather', 'soil', 'other'];
    public const SEVERITIES = ['low', 'medium', 'high', 'critical'];
    public const STATUSES = ['open', 'treating', 'resolved', 'cancelled'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function workTask(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function farmingLog(): BelongsTo
    {
        return $this->belongsTo(FarmingLog::class, 'farming_log_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function reportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function chemicalUsages(): HasMany
    {
        return $this->hasMany(ChemicalUsage::class);
    }
}
