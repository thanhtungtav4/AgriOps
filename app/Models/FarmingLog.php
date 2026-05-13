<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmingLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'work_task_id',
        'planting_batch_id',
        'planting_batch_allocation_id',
        'plot_id',
        'bed_id',
        'reported_by_user_id',
        'client_uuid',
        'logged_at',
        'status',
        'actual_start_at',
        'actual_end_at',
        'notes',
        'photo_paths',
        'metadata',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'photo_paths' => 'array',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'submitted',
    ];

    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function workTask(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlantingBatchAllocation::class, 'planting_batch_allocation_id');
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
}
