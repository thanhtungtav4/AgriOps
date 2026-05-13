<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'entity_type',
        'entity_id',
        'user_id',
        'actor_type',
        'farm_id',
        'from_status',
        'to_status',
        'plot_id',
        'bed_id',
        'allocated_area_m2',
        'metadata',
        'reason',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'user_id' => 'integer',
        'farm_id' => 'integer',
        'plot_id' => 'integer',
        'bed_id' => 'integer',
        'allocated_area_m2' => 'decimal:2',
        'metadata' => 'array',
    ];

    public const TYPE_LIFECYCLE_TRANSITION = 'lifecycle_transition';
    public const TYPE_ALLOCATION_CREATED = 'allocation_created';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public static function recordLifecycleTransition(
        int $farmId,
        int $batchId,
        string $fromStatus,
        string $toStatus,
        ?int $userId = null,
        ?string $actorType = null,
        ?string $reason = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'event_type' => self::TYPE_LIFECYCLE_TRANSITION,
            'entity_type' => 'PlantingBatch',
            'entity_id' => $batchId,
            'user_id' => $userId,
            'actor_type' => $actorType ?? 'user',
            'farm_id' => $farmId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    public static function recordAllocationCreated(
        int $farmId,
        int $allocationId,
        int $batchId,
        int $plotId,
        ?int $bedId = null,
        ?float $allocatedAreaM2 = null,
        ?int $userId = null,
        ?string $actorType = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'event_type' => self::TYPE_ALLOCATION_CREATED,
            'entity_type' => 'PlantingBatchAllocation',
            'entity_id' => $allocationId,
            'user_id' => $userId,
            'actor_type' => $actorType ?? 'user',
            'farm_id' => $farmId,
            'plot_id' => $plotId,
            'bed_id' => $bedId,
            'allocated_area_m2' => $allocatedAreaM2,
            'metadata' => $metadata,
        ]);
    }
}