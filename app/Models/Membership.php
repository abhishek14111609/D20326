<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'transaction_id',
        'purchase_date',
        'expiry_date',
        'platform',
        'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date'
    ];

    /**
     * Get the user who owns the membership
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this membership
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
