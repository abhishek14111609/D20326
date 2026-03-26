<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    const TYPE_MESSAGE = 'message';
    const TYPE_MATCH = 'match';
    const TYPE_LIKE = 'like';
    const TYPE_COMMENT = 'comment';
    const TYPE_FOLLOW = 'follow';
    const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'message',
        'read_at',
        'data'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array'
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who triggered the notification
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Scope a query to only include unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark the notification as read
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
}
