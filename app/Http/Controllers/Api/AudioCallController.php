<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AudioCallService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Notification;
use App\Jobs\SendPushNotificationJob;
use Illuminate\Support\Facades\Log;

class AudioCallController extends Controller
{
    protected $callService;

    public function __construct(AudioCallService $callService)
    {
        $this->callService = $callService;
    }

    /**
     * Initiate Call
     */
public function initiate(Request $request): JsonResponse
{
    $request->validate([
        'caller_id'   => 'required|integer',
        'receiver_id' => 'required|integer',
    ]);

    try {
        $caller   = User::findOrFail($request->caller_id);
        $receiver = User::findOrFail($request->receiver_id);

        // Create call record
        $call = $this->callService->initiateCall(
            $caller->id,
            $receiver->id
        );

        // 1️⃣ Create DB Notification
        $notification = Notification::create([
            'user_id' => $receiver->id,
            'type' => Notification::TYPE_SYSTEM,
            'message' => "Incoming call from " . $caller->name,
            'data' => [
                'notification_type' => 'incoming_call',
                'caller_id' => $caller->id,
                'caller_name' => $caller->name,
                'call_id' => $call->id,
            ]
        ]);

        // 2️⃣ Send Push Notification
        $this->sendCallNotification($receiver, $caller, $notification);

        return response()->json([
            'success' => true,
            'message' => 'Call initiated successfully',
            'data' => $call
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}


protected function sendCallNotification(User $receiver, User $caller, Notification $notification)
{
    try {
        $fcmToken = $receiver->fcm_token
            ?? $receiver->fcmTokens()->latest()->first()?->token;

        if (!$fcmToken) {
            Log::info("No FCM token for call", ['user_id' => $receiver->id]);
            return;
        }

        $fcmService = app(\App\Services\FcmService::class);

        $title = "Incoming Call";
        $body  = "Call from " . $caller->name;

        // SAME PROFILE WORKING FORMAT
        $payload = [
            "message" => [
                "token" => $fcmToken,

                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ],

                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "sound" => "default"
                    ],
                ],

                "apns" => [
                    "headers" => [
                        "apns-priority" => "10"
                    ],
                    "payload" => [
                        "aps" => [
                            "sound" => "default"
                        ]
                    ]
                ],

                "data" => [
                    "type" => "incoming_call",
                    "notification_id" => (string)$notification->id,
                    "call_id" => (string)$notification->data['call_id'],
                    "caller_id" => (string)$caller->id,
                    "caller_name" => $caller->name,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ]
            ]
        ];

        // Send to Firebase
        $fcmService->sendNotification(
            $fcmToken,
            $title,
            $body,
            $payload["message"]["data"]
        );

        Log::info("CALL FCM SENT", [
            'receiver_id' => $receiver->id
        ]);

    } catch (\Exception $e) {
        Log::error("Call FCM failed", ['error' => $e->getMessage()]);
    }
}


    /**
     * Accept Call
     */
    public function accept(Request $request): JsonResponse
    {
        $request->validate([
            'call_id' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        try {
            $call = $this->callService->acceptCall(
                $request->call_id,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Call accepted',
                'data' => $call
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * End Call
     */
    public function end(Request $request): JsonResponse
    {
        $request->validate([
            'call_id' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        try {
            $call = $this->callService->endCall(
                $request->call_id,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Call ended successfully',
                'data' => $call
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reject Call
     */
    public function reject(Request $request): JsonResponse
    {
        $request->validate([
            'call_id' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        try {
            $call = $this->callService->rejectCall(
                $request->call_id,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Call rejected',
                'data' => $call
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Call Stats
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'call_id' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        try {
            $stats = $this->callService->getCallStats(
                $request->call_id,
                $request->user_id
            );

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
