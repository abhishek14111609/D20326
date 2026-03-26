<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FcmService;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sender;
    protected $receiverId;
    protected $messageData;

    public function __construct($sender, $receiverId, $messageData)
    {
        $this->sender = $sender;
        $this->receiverId = $receiverId;
        $this->messageData = $messageData;
    }

    public function handle(): void
    {
        try {
            $receiver = User::find($this->receiverId);

            if (!$receiver) {
                Log::warning('Receiver not found', [
                    'receiver_id' => $this->receiverId
                ]);
                return;
            }

            $type = $this->messageData['data']['type'] ?? 'message';

            // 1️⃣ Create App Notification
            $this->createInAppNotification($receiver, $type);

            // 2️⃣ Send FCM
            $this->sendFcmNotification($receiver, $this->sender, $this->messageData);

        } catch (\Exception $e) {
            Log::error('Push job failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function createInAppNotification($receiver, $type)
    {
        $notificationService = app(FirebaseNotificationService::class);
		
        if ($type === 'profile_updated') {

            return $notificationService->create(
                $receiver,
                'system',
                'Your profile was updated successfully!',
                $this->sender,
                [
                    'updated_fields' => $this->messageData['data']['updated_fields'] ?? [],
                    'updated_at' => $this->messageData['data']['updated_at'] ?? now()->toDateTimeString(),
                    'type' => 'profile_updated'
                ]
            );
        }
	
        // NORMAL MESSAGE
        return $notificationService->create(
            $receiver,
            'message',
            'New message from ' . $this->sender->name,
            $this->sender,
            [
                'message_id' => $this->messageData['uuid'],
                'message_preview' => $this->getMessagePreview(),
                'sender_name' => $this->sender->name,
                'sender_avatar' => $this->sender->profile_image,
                'type' => 'message'
            ]
        );
    }

    protected function getMessagePreview()
    {
        $text = $this->messageData['message'] ?? '';

        return match ($this->messageData['message_type']) {
            'media' => '📷 Media message',
            'voice' => '🎵 Voice message',
            'location' => '📍 Location shared',
            'system' => $text,
            default => Str::limit($text, 50),
        };
    }

    protected function sendFcmNotification($receiver, $sender, $messageData)
    {
        try {
            // Get latest FCM token
            $fcmToken = $receiver->fcm_token
                ?? $receiver->fcmTokens()->latest()->first()?->token;
			
            if (!$fcmToken) {
                Log::info('User has no FCM token', ['user_id' => $receiver->id]);
                return;
            }

            $type = $messageData['data']['type'] ?? 'message';
			
            // --- Notification (visible) ---
            if ($type === 'profile_updated') {

                $notificationPayload = [
                    'title' => 'Profile Updated',
                    'body' => 'Your profile was updated successfully!',
                    'sound' => 'default'
                ];

                $dataPayload = [
                    'type' => 'profile_updated',
                    'updated_fields' => $messageData['data']['updated_fields'] ?? [],
                    'updated_at' => $messageData['data']['updated_at'] ?? now()->toDateTimeString(),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ];

            } else {
                // Message
                $notificationPayload = [
                    'title' => $sender->name,
                    'body' => $this->getMessagePreview(),
                    'sound' => 'default'
                ];

                $dataPayload = [
                    'type' => 'message',
                    'sender_id' => $sender->id,
                    'message_uuid' => $messageData['uuid'],
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ];
            }
			
            app(FcmService::class)->sendToDevice(
                $fcmToken,
                [
                    'notification' => $notificationPayload,
                    'data' => $dataPayload
                ]
            );

        } catch (\Exception $e) {
            Log::error('FCM error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
