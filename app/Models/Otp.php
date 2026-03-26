<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mobile',
        'otp_code',
        'type',
        'status',
        'expires_at',
        'attempts',
        'ip_address',
        'verified_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
        'data' => 'array'
    ];

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Mark OTP as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now()
        ]);
    }

    /**
     * Mark OTP as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Scope for pending OTPs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for valid (not expired and pending) OTPs
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
                    ->where('expires_at', '>', now());
    }
}
