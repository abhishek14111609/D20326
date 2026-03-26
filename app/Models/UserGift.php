<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGift extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'gift_id',
        'message',
        'price',
        'is_anonymous',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'is_anonymous' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_price',
        'sent_at',
    ];

    /**
     * Get the user who sent the gift.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'name' => 'Anonymous',
            'avatar' => asset('images/default-avatar.png'),
        ]);
    }

    /**
     * Get the user who received the gift.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the gift that was sent.
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    /**
     * Get the transaction associated with this gift.
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'reference');
    }

    /**
     * Get the formatted price attribute.
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    /**
     * Get the sent at timestamp in a human-readable format.
     *
     * @return string
     */
    public function getSentAtAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope a query to only include gifts sent by a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope a query to only include gifts received by a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    /**
     * Scope a query to only include gifts of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $giftId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $giftId)
    {
        return $query->where('gift_id', $giftId);
    }

    /**
     * Scope a query to only include anonymous gifts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    /**
     * Scope a query to only include non-anonymous gifts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotAnonymous($query)
    {
        return $query->where('is_anonymous', false);
    }
}
