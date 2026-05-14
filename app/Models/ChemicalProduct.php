<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChemicalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'active_ingredient',
        'type',
        'formulation',
        'registration_number',
        'manufacturer',
        'supplier',
        'unit',
        'stock_quantity',
        'min_stock_level',
        'price_per_unit',
        'usage_instructions',
        'safety_instructions',
        'storage_conditions',
        'expiry_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'stock_quantity' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'price_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'pesticide' => 'pesticide',
        'herbicide' => 'herbicide',
        'fungicide' => 'fungicide',
        'fertilizer' => 'fertilizer',
        'biological' => 'biological',
        'other' => 'other',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<', 'min_stock_level');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    // ─── Helpers ───────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity < $this->min_stock_level;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}