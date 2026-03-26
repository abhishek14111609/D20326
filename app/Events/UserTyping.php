<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $typingUserId;
    public $receiverId;
    public $isTyping;

    /**
     * Create a new event instance.
     *
     * @param int $typingUserId
     * @param int $receiverId
     * @param bool $isTyping
     * @return void
     */
    public function __construct($typingUserId, $receiverId, $isTyping)
    {
        $this->typingUserId = $typingUserId;
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
        return new PrivateChannel('chat.' . $this->receiverId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'user.typing';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'typing_user_id' => $this->typingUserId,
            'is_typing' => $this->isTyping,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
