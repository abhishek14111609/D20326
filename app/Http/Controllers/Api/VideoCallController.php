<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitiateVideoCallRequest;
use App\Http\Requests\Api\VideoCallActionRequest;
use App\Http\Requests\Api\VideoGetAgoraTokenRequest;
use App\Services\VideoCallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Notification;
use App\Jobs\SendPushNotificationJob;

class VideoCallController extends Controller
{
    protected $videoCallService;

    public function __construct(VideoCallService $videoCallService)
    {
        $this->videoCallService = $videoCallService;
    }

    /**
     * Initiate a new video call
     */
public function initiate(InitiateVideoCallRequest $request): JsonResponse
{
    try {
        $caller   = $request->user();
        $receiver = User::findOrFail($request->receiver_id);

        // ==============================
        // 1️⃣ Create Call
        // ==============================
        $call = $this->videoCallService->initiateCall(
            $caller->id,
            $receiver->id
        );

        // ==============================
        // 2️⃣ Create DB Notification
        // ==============================
        $notification = Notification::create([
            'user_id' => $receiver->id,
            'type' => Notification::TYPE_SYSTEM,
            'message' => "Incoming video call from " . $caller->name,
            'data' => [
                'notification_type' => 'incoming_video_call',
                'caller_id' => $caller->id,
                'caller_name' => $caller->name,
                'call_id' => $call->id,
            ]
        ]);

        // ==============================
        // 3️⃣ Send Push Notification
        // ==============================
        $this->sendVideoCallNotification($receiver, $caller, $notification);

        return response()->json([
            'success' => true,
            'message' => 'Video call initiated',
            'data' => $call
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}


protected function sendVideoCallNotification(User $receiver, User $caller, Notification $notification)
{
    try {
        $fcmToken = $receiver->fcm_token
            ?? $receiver->fcmTokens()->latest()->first()?->token;

        if (!$fcmToken) {
            Log::info("No FCM token for video call", ['receiver_id' => $receiver->id]);
            return;
        }

        $fcmService = app(\App\Services\FcmService::class);

        $title = "Incoming Video Call";
        $body  = "Video call from " . $caller->name;

        // SAME PROFILE UPDATE WORKING FORMAT
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
                    "type" => "incoming_video_call",
                    "notification_id" => (string)$notification->id,
                    "call_id" => (string)$notification->data['call_id'],
                    "caller_id" => (string)$caller->id,
                    "caller_name" => $caller->name,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ]
            ]
        ];

        // SEND FCM
        $fcmService->sendNotification(
            $fcmToken,
            $title,
            $body,
            $payload["message"]["data"]
        );

        Log::info("VIDEO CALL FCM SENT", [
            'receiver_id' => $receiver->id
        ]);

    } catch (\Exception $e) {
        Log::error("Video Call FCM failed", ['error' => $e->getMessage()]);
    }
}


    /**
     * Accept an incoming video call
     */
    public function accept(VideoCallActionRequest $request, string $callId): JsonResponse
    {
        try {
            $call = $this->videoCallService->acceptCall($callId, $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Video call accepted',
                'data' => $call
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * End an ongoing video call
     */
    public function end(VideoCallActionRequest $request, string $callId): JsonResponse
    {
        try {
            $call = $this->videoCallService->endCall($callId, $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Video call ended',
                'data' => $call
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Toggle video on/off during a call
     */
    public function toggleVideo(Request $request, string $callId): JsonResponse
    {
        $request->validate([
            'enable' => 'required|boolean'
        ]);

        try {
            $call = $this->videoCallService->toggleVideo(
                $callId, 
                $request->user()->id, 
                $request->enable
            );
            
            return response()->json([
                'success' => true,
                'message' => $request->enable ? 'Video enabled' : 'Video disabled',
                'data' => $call
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get Agora token for video call
     */
    public function getAgoraToken(VideoGetAgoraTokenRequest $request, string $callId): JsonResponse
    {
        try {
            Log::info('🔍 [VideoCallController] Getting Agora token', [
                'call_id' => $callId,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
            
            $config = $this->videoCallService->getAgoraConfig(
                $callId, 
                $request->user()->id
            );
            
            Log::info('✅ [VideoCallController] Successfully generated Agora token', [
                'call_id' => $callId,
                'user_id' => $request->user()->id,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [VideoCallController] Error getting Agora token', [
                'call_id' => $callId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mute the video call
     */
    public function mute(VideoCallActionRequest $request, string $callId): JsonResponse
    {
        try {
            $call = $this->videoCallService->muteCall($callId, $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Video call muted',
                'data' => $call
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unmute the video call
     */
    public function unmute(VideoCallActionRequest $request, string $callId): JsonResponse
    {
        try {
            $call = $this->videoCallService->unmuteCall($callId, $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Video call unmuted',
                'data' => $call
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    protected function setupMiddleware()
    {
        $this->middleware('auth:sanctum')->except(['getAgoraToken']);
    }
}
