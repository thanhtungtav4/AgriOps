<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessingRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'harvest_lot_id',
        'processed_by_user_id',
        'processed_at',
        'input_quantity',
        'output_quantity',
        'loss_quantity',
        'loss_rate',
        'unit',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'input_quantity' => 'decimal:3',
        'output_quantity' => 'decimal:3',
        'loss_quantity' => 'decimal:3',
        'loss_rate' => 'decimal:4',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'unit' => 'kg',
        'status' => 'completed',
        'loss_quantity' => 0,
        'loss_rate' => 0,
    ];

    public const STATUSES = ['draft', 'completed', 'cancelled'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function harvestLot(): BelongsTo
    {
        return $this->belongsTo(HarvestLot::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}