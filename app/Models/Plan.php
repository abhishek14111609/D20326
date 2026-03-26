<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'features'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array'
    ];

    /**
     * Get the memberships for this plan
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
