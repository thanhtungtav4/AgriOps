<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'breakdown_type',
        'production_plan_id',
        'planting_batch_id',
        'period_start',
        'period_end',
        'period_label',
        'total_seed_cost',
        'total_fertilizer_cost',
        'total_chemical_cost',
        'total_water_cost',
        'total_labor_cost',
        'total_machinery_cost',
        'total_land_rent_cost',
        'total_other_cost',
        'total_cost',
        'total_yield_kg',
        'cost_per_kg',
        'total_revenue',
        'gross_margin',
        'gross_margin_percent',
        'breakdown_by_subcategory',
        'cost_trends',
        'variances',
        'calculated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_seed_cost' => 'decimal:2',
        'total_fertilizer_cost' => 'decimal:2',
        'total_chemical_cost' => 'decimal:2',
        'total_water_cost' => 'decimal:2',
        'total_labor_cost' => 'decimal:2',
        'total_machinery_cost' => 'decimal:2',
        'total_land_rent_cost' => 'decimal:2',
        'total_other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_yield_kg' => 'decimal:3',
        'cost_per_kg' => 'decimal:4',
        'total_revenue' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'gross_margin_percent' => 'decimal:2',
        'breakdown_by_subcategory' => 'array',
        'cost_trends' => 'array',
        'variances' => 'array',
    ];

    const BREAKDOWN_TYPES = [
        'farm' => 'farm',
        'production_plan' => 'production_plan',
        'planting_batch' => 'planting_batch',
        'seasonal' => 'seasonal',
    ];

    const COST_CATEGORIES = [
        'seed',
        'fertilizer',
        'chemical',
        'water',
        'labor',
        'machinery',
        'land_rent',
        'other',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    // ─── Accessors for cost categories ──────────────────────────

    public function getCostByCategory(string $category): float
    {
        $fieldMap = [
            'seed' => 'total_seed_cost',
            'fertilizer' => 'total_fertilizer_cost',
            'chemical' => 'total_chemical_cost',
            'water' => 'total_water_cost',
            'labor' => 'total_labor_cost',
            'machinery' => 'total_machinery_cost',
            'land_rent' => 'total_land_rent_cost',
            'other' => 'total_other_cost',
        ];

        $field = $fieldMap[$category] ?? 'total_other_cost';

        return (float) $this->$field;
    }

    public function getCostPercentages(): array
    {
        if ($this->total_cost <= 0) {
            return [];
        }

        $total = $this->total_cost;
        return [
            'seed' => round(($this->total_seed_cost / $total) * 100, 1),
            'fertilizer' => round(($this->total_fertilizer_cost / $total) * 100, 1),
            'chemical' => round(($this->total_chemical_cost / $total) * 100, 1),
            'water' => round(($this->total_water_cost / $total) * 100, 1),
            'labor' => round(($this->total_labor_cost / $total) * 100, 1),
            'machinery' => round(($this->total_machinery_cost / $total) * 100, 1),
            'land_rent' => round(($this->total_land_rent_cost / $total) * 100, 1),
            'other' => round(($this->total_other_cost / $total) * 100, 1),
        ];
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeForFarm($query, int $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->where('period_start', '>=', $start)
            ->where('period_end', '<=', $end);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('breakdown_type', $type);
    }
}