<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMembership extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'status',
        'auto_renew',
        'payment_method',
        'transaction_id',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'is_active',
        'days_remaining',
        'status_label',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLING = 'cancelling';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PAUSED = 'paused';
    const STATUS_PAYMENT_FAILED = 'payment_failed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<=', now())
            ->orWhere('status', self::STATUS_EXPIRED);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === self::STATUS_ACTIVE && 
               $this->ends_at?->isFuture();
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->ends_at || $this->ends_at->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->ends_at);
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function cancel($immediate = false)
    {
        if ($this->status === self::STATUS_CANCELLED) return false;

        $update = [
            'status' => $immediate ? self::STATUS_CANCELLED : self::STATUS_CANCELLING,
            'cancelled_at' => now(),
            'auto_renew' => false
        ];

        if ($immediate) {
            $update['ends_at'] = now();
        }

        return $this->update($update);
    }

    public function renew()
    {
        if (!$this->auto_renew || $this->status === self::STATUS_CANCELLED) {
            return false;
        }

        $newEnd = $this->ends_at->copy()->add(
            $this->plan->duration_value,
            $this->plan->duration_unit
        );

        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => $newEnd,
            'cancelled_at' => null,
        ]);
    }

    public function canRenew()
    {
        return $this->auto_renew &&
               $this->status !== self::STATUS_CANCELLED &&
               $this->ends_at?->isFuture();
    }

    public function getProviderPlanId()
    {
        return $this->plan?->getProviderPlanId($this->payment_method);
    }
}
