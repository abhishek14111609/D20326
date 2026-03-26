<?php

namespace App\Events;

use App\Models\AudioCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The audio call instance.
     *
     * @var \App\Models\AudioCall
     */
    public $call;

    /**
     * The event type (e.g., 'incoming_call', 'call_accepted', 'call_ended').
     *
     * @var string
     */
    public $eventType;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\AudioCall  $call
     * @param  string  $eventType
     * @return void
     */
    public function __construct(AudioCall $call, string $eventType)
    {
        $this->call = $call;
        $this->eventType = $eventType;
        
        // Don't include the entire call model in the broadcast
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to both caller and receiver's private channels
        return [
            new PrivateChannel('user.' . $this->call->caller_id),
            new PrivateChannel('user.' . $this->call->receiver_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'call_id' => $this->call->call_id,
            'event_type' => $this->eventType,
            'status' => $this->call->status,
            'caller_id' => $this->call->caller_id,
            'receiver_id' => $this->call->receiver_id,
            'is_muted' => $this->call->is_muted,
            'started_at' => $this->call->started_at,
            'ended_at' => $this->call->ended_at,
            'duration' => $this->call->duration,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'call.status.updated';
    }
}
