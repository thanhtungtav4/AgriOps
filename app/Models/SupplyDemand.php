<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyDemand extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'supply_contract_id',
        'crop_id',
        'quantity',
        'unit',
        'frequency',
        'target_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(SupplyContract::class, 'supply_contract_id');
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function productionPlans(): HasMany
    {
        return $this->hasMany(ProductionPlan::class);
    }
}
