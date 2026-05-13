<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTable extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'crop_id',
        'variety_id',
        'unit',
        'grade_a_price',
        'grade_b_price',
        'grade_c_price',
        'side_channel_price',
        'effective_from',
        'effective_until',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'grade_a_price' => 'decimal:2',
        'grade_b_price' => 'decimal:2',
        'grade_c_price' => 'decimal:2',
        'side_channel_price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'metadata' => 'array',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'variety_id');
    }

    public function scopeActiveForDate(Builder $query, string $date): Builder
    {
        return $query
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $date)
            ->where(function (Builder $query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $date);
            });
    }
}
