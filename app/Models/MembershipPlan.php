<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'duration_value',
        'duration_unit',
        'level',
        'is_active',
        'features',
        'stripe_plan_id',
        'paypal_plan_id',
        'razorpay_plan_id',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_value' => 'integer',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    protected $appends = [
        'formatted_price',
        'duration_label',
    ];

    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class);
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function getDurationLabelAttribute()
    {
        $unit = $this->duration_value > 1 ? str_plural($this->duration_unit) : $this->duration_unit;
        return "{$this->duration_value} {$unit}";
    }

    public function getProviderPlanId($provider)
    {
        $provider = strtolower($provider);
        $column = "{$provider}_plan_id";
        return $this->{$column};
    }
}
