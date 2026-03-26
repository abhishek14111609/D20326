<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiftCategory extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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
        'icon_url',
    ];

    /**
     * Get the gifts for the category.
     */
    public function gifts()
    {
        return $this->hasMany(Gift::class, 'category_id');
    }

    /**
     * Get the URL for the category icon.
     *
     * @return string
     */
    public function getIconUrlAttribute()
    {
        if (!$this->icon) {
            return asset('images/default-category-icon.png');
        }

        return Storage::disk('public')->url($this->icon);
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order categories by sort order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query, $direction = 'asc')
    {
        return $query->orderBy('sort_order', $direction);
    }

    /**
     * Get the active gifts count for the category.
     *
     * @return int
     */
    public function getActiveGiftsCountAttribute()
    {
        return $this->gifts()->active()->count();
    }
}
