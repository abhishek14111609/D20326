<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    /**
     * The types of messages
     */
    const TYPE_TEXT = 'text';
    const TYPE_MEDIA = 'media';
    const TYPE_SYSTEM = 'system';

    /**
     * The media types for media messages
     */
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_VIDEO = 'video';
    const MEDIA_TYPE_AUDIO = 'audio';
    const MEDIA_TYPE_DOCUMENT = 'document';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'media_path',
        'media_type',
        'read_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'read_at' => 'datetime',
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
        'is_sender',
        'media_url',
    ];

    /**
     * Get the sender of the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the conversation that the message belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Check if the message is read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the message as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            $this->read_at = now();
            return $this->save();
        }
        return false;
    }

    /**
     * Check if the message is from the given user.
     *
     * @param  int|\App\Models\User  $user
     * @return bool
     */
    public function isFrom($user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->sender_id == $userId;
    }

    /**
     * Check if the message is to the given user.
     *
     * @param  int|\App\Models\User  $user
     * @return bool
     */
    public function isTo($user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->receiver_id == $userId;
    }

    /**
     * Get the full URL for the media file.
     *
     * @return string|null
     */
    public function getMediaUrlAttribute(): ?string
    {
        if (!$this->media_path) {
            return null;
        }

        return Storage::disk('public')->url($this->media_path);
    }

    /**
     * Check if the authenticated user is the sender of the message.
     *
     * @return bool
     */
    public function getIsSenderAttribute(): bool
    {
        return $this->sender_id === auth()->id();
    }

    /**
     * Scope a query to only include unread messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include messages between two users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId1
     * @param  int  $userId2
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)
              ->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)
              ->where('receiver_id', $userId1);
        });
    }

    /**
     * Scope a query to only include messages for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('sender_id', $userId)
                     ->orWhere('receiver_id', $userId);
    }
}
