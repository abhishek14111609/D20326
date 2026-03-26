<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RtmService;
use App\Services\AgoraService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnhancedRtmController extends Controller
{
    protected $rtmService;
    protected $agoraService;
    protected $notificationService;

    public function __construct(
        RtmService $rtmService,
        AgoraService $agoraService,
        NotificationService $notificationService
    ) {
        $this->rtmService = $rtmService;
        $this->agoraService = $agoraService;
        $this->notificationService = $notificationService;
    }

    /**
     * Send instant message with zero loading
     */
    public function sendInstantMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:4000',
            'message_type' => 'string|in:text,media,system,voice,location',
            'metadata' => 'array',
            'client_timestamp' => 'required|integer',
            'uuid' => 'required|string|uuid',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $sender = $request->user();
        $receiverId = $request->input('receiver_id');
        
        if ($sender->id === $receiverId) {
            return $this->errorResponse('Cannot send message to yourself', 400);
        }

        try {
            // Generate message data for instant delivery
            $messageData = [
                'uuid' => $request->input('uuid'),
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'message' => $request->input('message'),
                'message_type' => $request->input('message_type', 'text'),
                'metadata' => $request->input('metadata', []),
                'client_timestamp' => $request->input('client_timestamp'),
                'server_timestamp' => now()->timestamp,
                'status' => 'sending',
                'sender' => [
                    'id' => $sender->id,
                    'name' => $sender->name,
                    'profile_image' => $sender->profile_image ?? '/assets/img/default-avatar.png'
                ]
            ];

        // Store in cache for instant access (no DB delay)
        $this->cacheMessageForInstantDelivery($messageData);

        // Send via RTM for instant delivery
            $rtmPayload = [
                'type' => 'instant_message',
                'uuid' => $messageData['uuid'],
                'sender_id' => $messageData['sender_id'],
                'receiver_id' => $messageData['receiver_id'],
                'message' => $messageData['message'],
                'message_type' => $messageData['message_type'],
                'metadata' => $messageData['metadata'],
                'timestamp' => $messageData['server_timestamp'],
                'sender' => $messageData['sender']
            ];

            // Return immediately for instant UI update
            $response = [
                'success' => true,
                'message_id' => $messageData['uuid'],
                'rtm_payload' => $rtmPayload,
                'channel' => $this->agoraService->generateConversationChannel($sender->id, $receiverId),
                'instant_delivery' => true,
                'server_timestamp' => $messageData['server_timestamp']
            ];

            // Background save to database (non-blocking)
            $this->saveMessageInBackground($messageData);

            // Send push notification in background
            $this->sendPushNotificationInBackground($sender, $receiverId, $messageData);

            return $this->successResponse($response, 'Message sent instantly');

        } catch (\Exception $e) {
            Log::error('Instant message send failed', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get instant messages (from cache first, then DB)
     */
    public function getInstantMessages(Request $request, $userId): JsonResponse
    {
        $validator = Validator::make(['userId' => $userId], [
            'userId' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $currentUser = $request->user();
        
        if ($currentUser->id === (int) $userId) {
            return $this->errorResponse('Cannot get conversation with yourself', 400);
        }

        try {
            // Try to get from cache first (instant)
            $cachedMessages = $this->getCachedConversationMessages($currentUser->id, (int) $userId);
            
            if (!empty($cachedMessages)) {
                return $this->successResponse([
                    'messages' => $cachedMessages,
                    'from_cache' => true,
                    'instant_load' => true
                ], 'Messages loaded instantly from cache');
            }

            // Fallback to database
            $messages = $this->rtmService->getConversationMessages(
                $currentUser->id,
                (int) $userId,
                1,
                50
            );

            return $this->successResponse([
                'messages' => $messages['messages'],
                'from_cache' => false,
                'instant_load' => false
            ], 'Messages loaded from database');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update message status instantly
     */
    public function updateMessageStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_uuid' => 'required|string|uuid',
            'status' => 'required|in:delivered,read',
            'timestamp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $messageUuid = $request->input('message_uuid');
        $status = $request->input('status');
        $timestamp = $request->input('timestamp');

        try {
            // Update in cache instantly
            $this->updateMessageStatusInCache($messageUuid, $status, $timestamp);

            // Update in database in background
            $this->updateMessageStatusInDatabase($messageUuid, $status, $timestamp);

            return $this->successResponse([
                'message_uuid' => $messageUuid,
                'status' => $status,
                'updated_at' => $timestamp,
                'instant_update' => true
            ], 'Message status updated instantly');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Send typing indicator
     */
    public function sendTypingIndicator(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'is_typing' => 'required|boolean',
            'timestamp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $sender = $request->user();
        $receiverId = $request->input('receiver_id');
        
        if ($sender->id === $receiverId) {
            return $this->errorResponse('Cannot send typing indicator to yourself', 400);
        }

        $typingIndicator = [
            'type' => 'typing_indicator',
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'is_typing' => $request->input('is_typing'),
            'timestamp' => $request->input('timestamp'),
            'channel' => $this->agoraService->generateConversationChannel($sender->id, $receiverId),
        ];

        return $this->successResponse($typingIndicator, 'Typing indicator sent');
    }

    /**
     * Get user presence status
     */
    public function getUserPresence(Request $request, $userId): JsonResponse
    {
        $validator = Validator::make(['userId' => $userId], [
            'userId' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        try {
            $presence = $this->getUserPresenceFromCache((int) $userId);

            return $this->successResponse($presence, 'User presence retrieved');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Cache message for instant delivery
     */
    protected function cacheMessageForInstantDelivery($messageData)
    {
        $cacheKey = "instant_message_{$messageData['uuid']}";
        $conversationKey = "conversation_cache_{$messageData['sender_id']}_{$messageData['receiver_id']}";
        
        // Store message with 1 hour TTL
        Cache::put($cacheKey, $messageData, 3600);
        
        // Add to conversation cache
        $existingMessages = Cache::get($conversationKey, []);
        array_unshift($existingMessages, $messageData);
        
        // Keep only last 100 messages in conversation cache
        if (count($existingMessages) > 100) {
            $existingMessages = array_slice($existingMessages, 0, 100);
        }
        
        Cache::put($conversationKey, $existingMessages, 3600);
    }

    /**
     * Get cached conversation messages
     */
    protected function getCachedConversationMessages($userId1, $userId2)
    {
        $conversationKey = "conversation_cache_{$userId1}_{$userId2}";
        $cached = Cache::get($conversationKey, []);
        
        if (!empty($cached)) {
            return $cached;
        }
        
        // Try reverse order
        $conversationKey = "conversation_cache_{$userId2}_{$userId1}";
        $cached = Cache::get($conversationKey, []);
        
        return $cached;
    }

    /**
     * Update message status in cache
     */
    protected function updateMessageStatusInCache($messageUuid, $status, $timestamp)
    {
        $cacheKey = "instant_message_{$messageUuid}";
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            $messageData = $cached;
            $messageData['status'] = $status;
            $messageData['status_updated_at'] = $timestamp;
            
            Cache::put($cacheKey, $messageData, 3600);
        }
    }

    /**
     * Update message status in database (background)
     */
    protected function updateMessageStatusInDatabase($messageUuid, $status, $timestamp)
    {
        // Queue job for background processing
        \App\Jobs\UpdateMessageStatusJob::dispatch($messageUuid, $status, $timestamp);
    }

    /**
     * Save message in database (background)
     */
    protected function saveMessageInBackground($messageData)
    {
        \App\Jobs\SaveMessageJob::dispatch($messageData);
    }

    /**
     * Send push notification in background
     */
    protected function sendPushNotificationInBackground($sender, $receiverId, $messageData)
    {
        \App\Jobs\SendPushNotificationJob::dispatch($sender, $receiverId, $messageData);
    }

    /**
     * Get user presence from cache
     */
    protected function getUserPresenceFromCache($userId)
    {
        $cacheKey = "user_presence_{$userId}";
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return $cached;
        }
        
        return [
            'user_id' => $userId,
            'is_online' => false,
            'last_seen' => null,
            'status' => 'offline'
        ];
    }

    /**
     * Success response helper
     */
    protected function successResponse($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->timestamp
        ]);
    }

    /**
     * Error response helper
     */
    protected function errorResponse($message, $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'timestamp' => now()->timestamp
        ], $statusCode);
    }
}
