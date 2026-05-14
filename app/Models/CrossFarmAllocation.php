<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrossFarmAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_farm_id',
        'production_plan_id',
        'reason',
        'supplement_farm_id',
        'planting_batch_id',
        'crop_id',
        'requested_quantity',
        'allocated_quantity',
        'unit',
        'packing_lot_code',
        'qr_code',
        'status',
        'notes',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:3',
        'allocated_quantity' => 'decimal:3',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'fulfilled' => 'fulfilled',
        'cancelled' => 'cancelled',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function sourceFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'source_farm_id');
    }

    public function supplementFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'supplement_farm_id');
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUSES['pending']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUSES['approved']);
    }

    public function scopeForFarm($query, int $farmId)
    {
        return $query->where('source_farm_id', $farmId)
            ->orWhere('supplement_farm_id', $farmId);
    }
}