<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingLotSource extends Model
{
    protected $fillable = [
        'packing_lot_id',
        'harvest_lot_id',
        'farm_id',
        'planting_batch_id',
        'quantity',
        'unit',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'unit' => 'kg',
    ];

    public function packingLot(): BelongsTo
    {
        return $this->belongsTo(PackingLot::class);
    }

    public function harvestLot(): BelongsTo
    {
        return $this->belongsTo(HarvestLot::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'planting_batch_id');
    }
}