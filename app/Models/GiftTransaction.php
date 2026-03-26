<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    protected $table = 'gift_transactions';

    protected $fillable = [
        'payment_intent_id',
        'sender_id',
        'receiver_id',
        'gift_id',
        'amount',
        'receiver_amount',
        'platform_amount',
        'status',
    ];
	
	
	public function gift()
	{
		return $this->belongsTo(Gift::class);
	}

    public function userGift()
    {
        return $this->hasOne(UserGift::class, 'gift_id', 'gift_id')
            ->whereColumn('user_gifts.receiver_id', 'gift_transactions.receiver_id');
    }

}
