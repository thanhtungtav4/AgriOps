<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingLot extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'created_by_user_id',
        'code',
        'packed_at',
        'status',
        'total_input_quantity',
        'total_output_quantity',
        'unit',
        'qr_code',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'packed_at' => 'datetime',
        'total_input_quantity' => 'decimal:3',
        'total_output_quantity' => 'decimal:3',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'unit' => 'kg',
        'total_input_quantity' => 0,
        'total_output_quantity' => 0,
    ];

    public const STATUSES = ['draft', 'packed', 'published', 'cancelled'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(PackingLotSource::class);
    }

    public function harvestLots(): HasMany
    {
        return $this->hasManyThrough(
            HarvestLot::class,
            PackingLotSource::class,
            'packing_lot_id',
            'id',
            'id',
            'harvest_lot_id'
        );
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }
}
