<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'address',
        'climate_zone',
        'responsible_person',
        'total_area_m2',
        'status',
        'certification',
        'image_url',
    ];

    protected $casts = [
        'total_area_m2' => 'decimal:2',
        'certification' => 'array',
    ];

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function processingRecords(): HasMany
    {
        return $this->hasMany(ProcessingRecord::class);
    }

    public function packingLots(): HasMany
    {
        return $this->hasMany(PackingLot::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

    public function priceTables(): HasMany
    {
        return $this->hasMany(PriceTable::class);
    }

    public function costRecords(): HasMany
    {
        return $this->hasMany(CostRecord::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
