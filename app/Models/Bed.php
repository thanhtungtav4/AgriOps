<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    use HasFactory;
    protected $fillable = [
        'plot_id',
        'code',
        'length_m',
        'width_m',
        'area_m2',
        'expected_plants',
        'status',
        'notes',
    ];

    protected $casts = [
        'length_m' => 'decimal:2',
        'width_m' => 'decimal:2',
        'area_m2' => 'decimal:2',
    ];

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
