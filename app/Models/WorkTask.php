<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'planting_batch_id',
        'planting_batch_allocation_id',
        'plot_id',
        'bed_id',
        'growth_stage_id',
        'assigned_user_id',
        'title',
        'task_type',
        'status',
        'priority',
        'planned_start_date',
        'planned_due_date',
        'started_at',
        'completed_at',
        'instructions',
        'completion_note',
        'metadata',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'planned',
        'priority' => 'normal',
    ];

    public const STATUSES = ['planned', 'assigned', 'in_progress', 'done', 'cancelled'];
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
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

    public function growthStage(): BelongsTo
    {
        return $this->belongsTo(GrowthStage::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeByFarm($query, int $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBatch($query, int $batchId)
    {
        return $query->where('planting_batch_id', $batchId);
    }

    public function scopeByGrowthStage($query, int $stageId)
    {
        return $query->where('growth_stage_id', $stageId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['planned', 'assigned', 'in_progress']);
    }

    public function farmingLogs(): HasMany
    {
        return $this->hasMany(FarmingLog::class, 'work_task_id');
    }
}
