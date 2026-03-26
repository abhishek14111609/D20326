<?php

namespace App\Notifications;

use App\Models\AudioCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The audio call instance.
     *
     * @var \App\Models\AudioCall
     */
    protected $call;

    /**
     * The event type.
     *
     * @var string
     */
    protected $eventType;

    /**
     * The notification message.
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\AudioCall  $call
     * @param  string  $eventType
     * @param  string  $message
     * @return void
     */
    public function __construct(AudioCall $call, string $eventType, string $message)
    {
        $this->call = $call;
        $this->eventType = $eventType;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // You can add more channels here like 'mail', 'sms', etc.
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/calls/' . $this->call->call_id);
        
        return (new MailMessage)
                    ->subject('Call Notification')
                    ->line($this->message)
                    ->action('View Call', $url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'call_id' => $this->call->call_id,
            'event_type' => $this->eventType,
            'message' => $this->message,
            'call' => [
                'id' => $this->call->call_id,
                'caller_id' => $this->call->caller_id,
                'receiver_id' => $this->call->receiver_id,
                'status' => $this->call->status,
                'is_muted' => $this->call->is_muted,
                'started_at' => $this->call->started_at,
                'ended_at' => $this->call->ended_at,
                'duration' => $this->call->duration,
            ]
        ];
    }
    
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toBroadcast($notifiable)
    {
        return [
            'id' => $this->id,
            'type' => get_class($this),
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];
    }
    
    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'call.notification';
    }
}
