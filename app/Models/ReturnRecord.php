<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'delivery_note_id',
        'packing_lot_id',
        'recorded_by_user_id',
        'returned_at',
        'quantity',
        'unit',
        'reason',
        'handling_action',
        'revenue_deduction',
        'evidence_photo_paths',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'quantity' => 'decimal:3',
        'revenue_deduction' => 'decimal:2',
        'evidence_photo_paths' => 'array',
        'metadata' => 'array',
    ];

    public const REASONS = [
        'bruised_wilted',
        'wrong_size',
        'wrong_weight',
        'bad_color',
        'not_uniform',
        'pest_disease',
        'packaging_error',
        'late_delivery',
        'short_quantity',
        'other',
    ];

    public const HANDLING_ACTIONS = [
        'discard',
        'sell_side_channel',
        'reprocess',
        'record_loss',
        'compensate_next_delivery',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function packingLot(): BelongsTo
    {
        return $this->belongsTo(PackingLot::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
