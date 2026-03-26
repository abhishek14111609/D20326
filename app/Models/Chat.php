<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'message_type',
        'is_read',
        'sent_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime'
    ];

    /**
     * Get the sender user
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver user
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
