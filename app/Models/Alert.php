<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;
    protected $fillable = [
        'farm_id',
        'recipient_user_id',
        'recipient_role',
        'alert_type',
        'severity',
        'title',
        'message',
        'source_type',
        'source_id',
        'context',
        'notification_payload',
        'status',
        'read_at',
    ];

    protected $casts = [
        'context' => 'array',
        'notification_payload' => 'array',
        'read_at' => 'datetime',
    ];

    public const STATUSES = ['unread', 'read', 'archived'];
    public const SEVERITIES = ['info', 'warning', 'critical'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
