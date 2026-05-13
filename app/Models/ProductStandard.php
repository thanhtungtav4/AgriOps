<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStandard extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'name',
        'code',
        'specifications',
        'allowed_defect_percent',
        'packing_spec',
        'grade',
    ];

    protected $casts = [
        'allowed_defect_percent' => 'decimal:2',
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
