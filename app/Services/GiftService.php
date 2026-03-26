<?php

namespace App\Services;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Support\Facades\DB;

class GiftService
{
    /**
     * Send a gift from one user to another
     *
     * @param int $senderId
     * @param int $receiverId
     * @param int $giftId
     * @param string|null $message
     * @return \App\Models\UserGift
     * @throws \Exception
     */
    public function sendGift($senderId, $receiverId, $giftId, $message = null)
    {
        return DB::transaction(function () use ($senderId, $receiverId, $giftId, $message) {
            // Get the gift with a lock to prevent race conditions
            $gift = Gift::lockForUpdate()->findOrFail($giftId);
            
            // Record the gift
            $userGift = UserGift::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'gift_id' => $giftId,
                'message' => $message,
                'gift_price' => $gift->price
            ]);
            
            // Load relationships for the response
            return $userGift->load('gift', 'sender', 'receiver');
        });
    }
    
    /**
     * Get all gifts received by a user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserReceivedGifts($userId)
    {
        return UserGift::with(['sender', 'gift'])
            ->where('receiver_id', $userId)
            ->latest()
            ->get();
    }
    
    /**
     * Get all gifts sent by a user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSentGifts($userId)
    {
        return UserGift::with(['receiver', 'gift'])
            ->where('sender_id', $userId)
            ->latest()
            ->get();
    }
    
    /**
     * Record a gift transaction in the transaction history
     *
     * @param \App\Models\User $sender
     * @param \App\Models\User $receiver
     * @param \App\Models\Gift $gift
     * @param \App\Models\UserGift $userGift
     * @return void
     */
    protected function recordGiftTransaction($sender, $receiver, $gift, $userGift)
    {
        // Record transaction for sender (debit)
        $sender->transactions()->create([
            'type' => 'gift_sent',
            'amount' => -$gift->price,
            'description' => "Gift sent to {$receiver->name}",
            'reference_id' => $userGift->id,
            'reference_type' => get_class($userGift),
        ]);
        
        // Record transaction for receiver (credit)
        $receiver->transactions()->create([
            'type' => 'gift_received',
            'amount' => $gift->price,
            'description' => "Gift received from {$sender->name}",
            'reference_id' => $userGift->id,
            'reference_type' => get_class($userGift),
        ]);
    }
    
    /**
     * Send a notification to the gift receiver
     *
     * @param \App\Models\User $receiver
     * @param \App\Models\User $sender
     * @param \App\Models\Gift $gift
     * @param \App\Models\UserGift $userGift
     * @return void
     */
    protected function sendGiftNotification($receiver, $sender, $gift, $userGift)
    {
        // Send in-app notification
        $receiver->notifications()->create([
            'type' => 'gift_received',
            'data' => [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'gift_id' => $gift->id,
                'gift_name' => $gift->name,
                'gift_image' => $gift->image_url,
                'message' => $userGift->message,
                'price' => $gift->price,
            ],
            'read_at' => null,
        ]);
        
        // Optionally send push notification
        if ($receiver->push_token) {
            // This would integrate with your push notification service
            // $this->pushNotificationService->send(
            //     $receiver->push_token,
            //     'New Gift Received',
            //     "You've received a gift from {$sender->name}!",
            //     [
            //         'type' => 'gift_received',
            //         'gift_id' => $gift->id,
            //         'sender_id' => $sender->id,
            //     ]
            // );
        }
    }
}
