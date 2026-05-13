<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborNorm extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'hours_per_m2',
        'cost_per_m2',
        'notes',
    ];

    protected $casts = [
        'hours_per_m2' => 'decimal:4',
        'cost_per_m2' => 'decimal:2',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }
}
