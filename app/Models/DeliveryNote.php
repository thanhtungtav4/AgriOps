<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'packing_lot_id',
        'supply_contract_id',
        'delivered_by_user_id',
        'code',
        'customer_name',
        'customer_type',
        'delivered_at',
        'planned_quantity',
        'accepted_quantity',
        'returned_quantity',
        'net_quantity',
        'unit',
        'unit_price',
        'gross_revenue',
        'return_deduction',
        'side_channel_revenue',
        'net_revenue',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'planned_quantity' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'net_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'gross_revenue' => 'decimal:2',
        'return_deduction' => 'decimal:2',
        'side_channel_revenue' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'metadata' => 'array',
    ];

    public const STATUSES = ['delivered', 'accepted', 'partially_returned', 'returned', 'cancelled'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function packingLot(): BelongsTo
    {
        return $this->belongsTo(PackingLot::class);
    }

    public function supplyContract(): BelongsTo
    {
        return $this->belongsTo(SupplyContract::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by_user_id');
    }

    public function acceptanceRecord(): HasOne
    {
        return $this->hasOne(DeliveryAcceptanceRecord::class);
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }
}
