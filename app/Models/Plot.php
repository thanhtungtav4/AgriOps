<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plot extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'code',
        'name',
        'area_m2',
        'soil_type',
        'water_source',
        'status',
        'current_crop_id',
        'current_batch_id',
        'notes',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }
}
