<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyContract extends Model
{
    protected $fillable = [
        'farm_id',
        'customer_name',
        'customer_type',
        'crop_id',
        'quantity',
        'unit',
        'frequency',
        'start_date',
        'end_date',
        'product_standard_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function productStandard(): BelongsTo
    {
        return $this->belongsTo(ProductStandard::class, 'product_standard_id');
    }

    public function demands(): HasMany
    {
        return $this->hasMany(SupplyDemand::class);
    }

    public function productionPlans(): HasMany
    {
        return $this->hasMany(ProductionPlan::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }
}
