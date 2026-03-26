<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user1_id',
        'user2_id',
        'last_message_id',
        'unread_count_user1',
        'unread_count_user2',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'other_user',
        'unread_count',
        'is_archived',
    ];

    /**
     * Get the user1 that owns the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Get the user2 that owns the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Get the last message in the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * Get all messages in the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the other user in the conversation.
     *
     * @return \App\Models\User|null
     */
    public function getOtherUserAttribute()
    {
        if (auth()->check()) {
            return $this->user1_id === auth()->id() ? $this->user2 : $this->user1;
        }
        return null;
    }

    /**
     * Get the unread message count for the authenticated user.
     *
     * @return int
     */
    public function getUnreadCountAttribute(): int
    {
        if (auth()->check()) {
            return $this->user1_id === auth()->id() 
                ? $this->unread_count_user1 
                : $this->unread_count_user2;
        }
        return 0;
    }

    /**
     * Check if the conversation is archived by the authenticated user.
     *
     * @return bool
     */
    public function getIsArchivedAttribute(): bool
    {
        if (auth()->check()) {
            $pivot = $this->participants()
                ->where('user_id', auth()->id())
                ->first();
                
            return $pivot ? $pivot->is_archived : false;
        }
        return false;
    }

    /**
     * Get the participants of the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['is_archived', 'muted_until', 'deleted_at'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include conversations for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user1_id', $userId)
                     ->orWhere('user2_id', $userId);
    }

    /**
     * Scope a query to only include conversations between two users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId1
     * @param  int  $userId2
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function($q) use ($userId1, $userId2) {
            $q->where('user1_id', $userId1)
              ->where('user2_id', $userId2);
        })->orWhere(function($q) use ($userId1, $userId2) {
            $q->where('user1_id', $userId2)
              ->where('user2_id', $userId1);
        });
    }

    /**
     * Mark all messages in the conversation as read for a specific user.
     *
     * @param  int  $userId
     * @return bool
     */
    public function markAsRead($userId): bool
    {
        if ($this->user1_id === $userId) {
            $this->unread_count_user1 = 0;
        } else {
            $this->unread_count_user2 = 0;
        }
        
        return $this->save();
    }

    /**
     * Increment the unread message count for a specific user.
     *
     * @param  int  $userId
     * @return bool
     */
    public function incrementUnreadCount($userId): bool
    {
        if ($this->user1_id === $userId) {
            $this->increment('unread_count_user1');
        } else {
            $this->increment('unread_count_user2');
        }
        
        return $this->save();
    }

    /**
     * Update the last message in the conversation.
     *
     * @param  \App\Models\Message  $message
     * @return bool
     */
    public function updateLastMessage(Message $message): bool
    {
        $this->last_message_id = $message->id;
        $this->last_message_at = $message->created_at;
        
        // Increment unread count for the receiver
        if ($message->receiver_id === $this->user1_id) {
            $this->increment('unread_count_user1');
        } else {
            $this->increment('unread_count_user2');
        }
        
        return $this->save();
    }
}
