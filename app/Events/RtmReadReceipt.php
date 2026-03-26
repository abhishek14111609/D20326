<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RtmReadReceipt implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;
    public $messageIds;
    public $readAt;

    /**
     * Create a new event instance.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param array $messageIds
     * @param string $readAt
     * @return void
     */
    public function __construct($senderId, $receiverId, $messageIds, $readAt)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->messageIds = $messageIds;
        $this->readAt = $readAt;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('rtm.user.' . $this->receiverId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'rtm.read.receipt';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'sender_id' => $this->senderId,
            'message_ids' => $this->messageIds,
            'read_at' => $this->readAt,
            'timestamp' => now()->timestamp,
        ];
    }
}
