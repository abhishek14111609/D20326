<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'payment_intent_id',
        'amount',
        'currency',
        'receipt_url',
        'payment_method',
        'status',
        'description',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'currency' => 'USD',
        'status' => 'pending',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function getFormattedAmountAttribute()
    {
        $amount = $this->amount / 100;
        return number_format($amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function isSuccessful()
    {
        return in_array($this->status, ['completed', 'succeeded']);
    }
}
