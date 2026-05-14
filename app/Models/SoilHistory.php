<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoilHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'plot_id',
        'bed_id',
        'record_type',
        'recorded_at',
        'ph',
        'nitrogen',
        'phosphorus',
        'potassium',
        'organic_matter',
        'moisture',
        'zinc',
        'iron',
        'manganese',
        'copper',
        'boron',
        'amendment_applied',
        'amendment_quantity',
        'amendment_unit',
        'source',
        'lab_name',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'ph' => 'decimal:2',
        'nitrogen' => 'decimal:2',
        'phosphorus' => 'decimal:2',
        'potassium' => 'decimal:2',
        'organic_matter' => 'decimal:2',
        'moisture' => 'decimal:2',
        'zinc' => 'decimal:2',
        'iron' => 'decimal:2',
        'manganese' => 'decimal:2',
        'copper' => 'decimal:2',
        'boron' => 'decimal:2',
        'amendment_quantity' => 'decimal:2',
    ];

    const RECORD_TYPES = [
        'test_result' => 'test_result',
        'amendment' => 'amendment',
        'reading' => 'reading',
    ];

    const SOURCES = [
        'lab' => 'lab',
        'manual' => 'manual',
    ];

    // ─── Relationships ──────────────────────────────────────────

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeTestResult($query)
    {
        return $query->where('record_type', self::RECORD_TYPES['test_result']);
    }

    public function scopeAmendment($query)
    {
        return $query->where('record_type', self::RECORD_TYPES['amendment']);
    }

    public function scopeForPlot($query, int $plotId)
    {
        return $query->where('plot_id', $plotId);
    }

    public function scopeForBed($query, int $bedId)
    {
        return $query->where('bed_id', $bedId);
    }
}