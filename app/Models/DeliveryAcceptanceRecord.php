<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAcceptanceRecord extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'accepted_by_name',
        'accepted_at',
        'accepted_quantity',
        'rejected_quantity',
        'evidence_photo_paths',
        'notes',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'accepted_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'evidence_photo_paths' => 'array',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
