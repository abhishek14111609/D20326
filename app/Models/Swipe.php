<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Swipe extends Model
{
    /**
     * The types of swipes
     */
    const TYPE_LIKE = 'like';
    const TYPE_DISLIKE = 'dislike';
    const TYPE_SUPER_LIKE = 'super_like';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'swiper_id',
        'swiped_id',
        'type',
        'matched'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'matched' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all valid swipe types
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_LIKE,
            self::TYPE_DISLIKE,
            self::TYPE_SUPER_LIKE,
        ];
    }

    /**
     * Check if a swipe type is valid
     *
     * @param string $type
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::getTypes());
    }

    /**
     * Scope a query to only include likes
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLikes($query)
    {
        return $query->where('type', self::TYPE_LIKE);
    }

    /**
     * Scope a query to only include super likes
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuperLikes($query)
    {
        return $query->where('type', self::TYPE_SUPER_LIKE);
    }

    /**
     * Scope a query to only include matches
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMatches($query)
    {
        return $query->where('is_match', true);
    }

    /**
     * Get the user who performed the swipe
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function swiper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    /**
     * Get the user who was swiped on
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function swiped(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }

    /**
     * Check if this swipe resulted in a match
     *
     * @return bool
     */
    public function isMatch(): bool
    {
        return $this->is_match === true;
    }

    /**
     * Mark this swipe as a match
     *
     * @return bool
     */
    public function markAsMatch(): bool
    {
        return $this->update(['is_match' => true]);
    }
}
