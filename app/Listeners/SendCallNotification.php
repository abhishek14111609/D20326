<?php

namespace App\Listeners;

use App\Events\CallStatusUpdated;
use App\Models\User;
use App\Notifications\CallNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SendCallNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\CallStatusUpdated  $event
     * @return void
     */
    public function handle(CallStatusUpdated $event)
    {
        $call = $event->call;
        $eventType = $event->eventType;

        $caller = User::find($call->caller_id);
        $receiver = User::find($call->receiver_id);

        if (! $caller || ! $receiver) {
            Log::warning("Call notification skipped due to missing users.", [
                'call_id' => $call->id,
                'caller_id' => $call->caller_id,
                'receiver_id' => $call->receiver_id,
            ]);
            return;
        }

        $recipient = null;
        $message = null;

        switch ($eventType) {
            case 'incoming_call':
                $recipient = $receiver;
                $message = "📞 Incoming call from {$caller->name}";
                break;

            case 'call_accepted':
                $recipient = $caller;
                $message = "✅ Your call has been accepted by {$receiver->name}";
                break;

            case 'call_rejected':
                $recipient = $caller;
                $message = "❌ Your call was rejected by {$receiver->name}";
                break;

            case 'call_ended':
                $currentUser = Auth::user();
                if ($currentUser) {
                    $recipient = $currentUser->id === $call->caller_id ? $receiver : $caller;
                } else {
                    // fallback — if no auth user in queue context
                    $recipient = $receiver;
                }

                $otherParty = $recipient->id === $caller->id ? $receiver->name : $caller->name;
                $message = "📴 Call with {$otherParty} has ended";
                break;

            case 'call_missed':
                // Caller should know the receiver missed the call
                $recipient = $caller;
                $message = "📵 Missed call from {$caller->name}";
                break;

            default:
                Log::info("Unknown call event type received.", [
                    'eventType' => $eventType,
                    'call_id' => $call->id,
                ]);
                return;
        }

        if ($recipient && $message) {
            Notification::send($recipient, new CallNotification(
                $call,
                $eventType,
                $message
            ));

            Log::info("Call notification sent successfully.", [
                'eventType' => $eventType,
                'call_id' => $call->id,
                'recipient_id' => $recipient->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \App\Events\CallStatusUpdated  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(CallStatusUpdated $event, $exception)
    {
        Log::error('❗ Failed to send call notification', [
            'call_id' => $event->call->id,
            'event_type' => $event->eventType,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
