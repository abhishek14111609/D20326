<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RtmMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverId;
    public $senderId;

    /**
     * Create a new event instance.
     *
     * @param array $message
     * @param int $receiverId
     * @param int $senderId
     * @return void
     */
    public function __construct($message, $receiverId, $senderId)
    {
        $this->message = $message;
        $this->receiverId = $receiverId;
        $this->senderId = $senderId;
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
        return 'rtm.message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'sender_id' => $this->senderId,
            'timestamp' => now()->timestamp,
        ];
    }
}
