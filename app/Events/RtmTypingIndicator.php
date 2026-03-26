<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RtmTypingIndicator implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId;
    public $receiverId;
    public $isTyping;

    /**
     * Create a new event instance.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param bool $isTyping
     * @return void
     */
    public function __construct($senderId, $receiverId, $isTyping)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->isTyping = $isTyping;
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
        return 'rtm.typing.indicator';
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
            'is_typing' => $this->isTyping,
            'timestamp' => now()->timestamp,
        ];
    }
}
