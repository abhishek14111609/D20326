<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gift extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'is_active',
        'category_id',
		'image_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Register the media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gifts')
             ->singleFile()
             ->useDisk('public');
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->hasMedia('gifts') 
            ? $this->getFirstMediaUrl('gifts') 
            : asset('assets/img/default-gift.png');
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->hasMedia('gifts')
            ? $this->getFirstMediaUrl('gifts', 'thumb')
            : asset('assets/img/default-gift-thumb.png');
    }

    /**
     * Get the category that owns the gift.
     */
    public function category()
    {
        return $this->belongsTo(GiftCategory::class, 'category_id');
    }

    /**
     * Get the users who have received this gift.
     */
    public function receivers()
    {
        return $this->belongsToMany(User::class, 'user_gifts', 'gift_id', 'receiver_id')
            ->withPivot(['sender_id', 'message', 'price'])
            ->withTimestamps();
    }

    /**
     * Get the users who have sent this gift.
     */
    public function senders()
    {
        return $this->belongsToMany(User::class, 'user_gifts', 'gift_id', 'sender_id')
            ->withPivot(['receiver_id', 'message', 'price'])
            ->withTimestamps();
    }

    /**
     * Get all user gift records for this gift.
     */
    public function userGifts()
    {
        return $this->hasMany(UserGift::class);
    }

    /**
     * Scope active gifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include gifts in the given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to order gifts by price.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }
}
