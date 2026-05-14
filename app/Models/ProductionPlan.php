<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'supply_contract_id',
        'supply_demand_id',
        'crop_id',
        'variety_id',
        'farm_id',
        'quantity',
        'unit',
        'target_delivery_date',
        'estimated_cost',
        'estimated_revenue',
        'estimated_margin',
        'margin_percent',
        'pricing_snapshot',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_delivery_date' => 'date',
        'quantity' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'estimated_revenue' => 'decimal:2',
        'estimated_margin' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'pricing_snapshot' => 'array',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function supplyContract(): BelongsTo
    {
        return $this->belongsTo(SupplyContract::class);
    }

    public function supplyDemand(): BelongsTo
    {
        return $this->belongsTo(SupplyDemand::class);
    }

    public function costRecords()
    {
        return $this->hasMany(CostRecord::class);
    }

    public function postSeasonReview()
    {
        return $this->hasOne(PostSeasonReview::class);
    }
}
