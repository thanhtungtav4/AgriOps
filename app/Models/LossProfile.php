<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LossProfile extends Model
{
    protected $fillable = [
        'crop_id',
        'variety_id',
        'harvest_loss_percent',
        'processing_loss_percent',
        'packing_loss_percent',
        'non_grade_a_percent',
        'reject_percent',
        'notes',
    ];

    protected $casts = [
        'harvest_loss_percent' => 'decimal:2',
        'processing_loss_percent' => 'decimal:2',
        'packing_loss_percent' => 'decimal:2',
        'non_grade_a_percent' => 'decimal:2',
        'reject_percent' => 'decimal:2',
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
