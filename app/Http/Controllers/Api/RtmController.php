<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RtmService;
use App\Services\AgoraService;
use App\Services\NotificationService;
use App\Models\User;
use App\Events\RtmMessageSent;
use App\Events\RtmTypingIndicator;
use App\Events\RtmReadReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="RTM Messaging",
 *     description="Real-time messaging endpoints using Agora RTM"
 * )
 */
class RtmController extends Controller
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
     * @OA\Get(
     *     path="/api/rtm/config",
     *     summary="Get RTM configuration for client",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="RTM configuration retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="app_id", type="string"),
     *                 @OA\Property(property="user_id", type="string"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="log_level", type="string", example="error")
     *             )
     *         )
     *     )
     * )
     */
    public function getConfig(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->successResponse(
            $this->agoraService->getRtmConfig($user->id),
            'RTM configuration retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/rtm/message",
     *     summary="Send a real-time message",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_id", "message"},
     *             @OA\Property(property="receiver_id", type="integer", example=123),
     *             @OA\Property(property="message", type="string", example="Hello!"),
     *             @OA\Property(property="message_type", type="string", example="text", enum={"text", "media", "system"}),
     *             @OA\Property(property="metadata", type="object", example={"media_path": "uploads/image.jpg", "media_type": "image"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message_id", type="integer"),
     *                 @OA\Property(property="conversation_id", type="integer"),
     *                 @OA\Property(property="rtm_payload", type="object"),
     *                 @OA\Property(property="channel", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'string|in:text,media,system',
            'metadata' => 'array',
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
            $result = $this->rtmService->sendRtmMessage(
                $sender->id,
                $receiverId,
                $request->input('message'),
                $request->input('message_type', 'text'),
                $request->input('metadata', [])
            );

            if (!$result['success']) {
                return $this->errorResponse($result['error'], 400);
            }

            // Send push notification to receiver
            $receiver = User::find($receiverId);
            $this->notificationService->create(
                $receiver,
                'message',
                'New message from ' . $sender->name,
                $sender,
                [
                    'message_id' => $result['message_id'],
                    'conversation_id' => $result['conversation_id'],
                    'preview' => Str::limit($request->input('message'), 100),
                    'rtm_channel' => $result['channel'],
                ]
            );

            // Broadcast message via Laravel broadcasting for fallback
            broadcast(new RtmMessageSent(
                $result['rtm_payload'],
                $receiverId,
                $sender->id
            ))->toOthers();

            return $this->successResponse($result, 'Message sent successfully');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/rtm/conversations",
     *     summary="Get user conversations",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conversations retrieved successfully"
     *     )
     * )
     */
    public function getConversations(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = min($request->input('per_page', 20), 50); // Max 50 per page

        $user = $request->user();
        $conversations = $this->rtmService->getUserConversations($user->id, $page, $perPage);

        return $this->successResponse($conversations, 'Conversations retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/rtm/conversations/{userId}/messages",
     *     summary="Get conversation messages",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="Other user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully"
     *     )
     * )
     */
    public function getConversationMessages(Request $request, $userId): JsonResponse
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

        $page = $request->input('page', 1);
        $perPage = min($request->input('per_page', 50), 100); // Max 100 per page

        $messages = $this->rtmService->getConversationMessages(
            $currentUser->id,
            (int) $userId,
            $page,
            $perPage
        );

        return $this->successResponse($messages, 'Messages retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/rtm/typing",
     *     summary="Send typing indicator",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_id", "is_typing"},
     *             @OA\Property(property="receiver_id", type="integer", example=123),
     *             @OA\Property(property="is_typing", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Typing indicator sent successfully"
     *     )
     * )
     */
    public function sendTypingIndicator(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'is_typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $sender = $request->user();
        $receiverId = $request->input('receiver_id');
        
        if ($sender->id === $receiverId) {
            return $this->errorResponse('Cannot send typing indicator to yourself', 400);
        }

        $typingIndicator = $this->rtmService->sendTypingIndicator(
            $sender->id,
            $receiverId,
            $request->input('is_typing')
        );

        // Broadcast typing indicator via Laravel broadcasting for fallback
        broadcast(new RtmTypingIndicator(
            $sender->id,
            $receiverId,
            $request->input('is_typing')
        ))->toOthers();

        return $this->successResponse($typingIndicator, 'Typing indicator sent successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/rtm/read-receipt",
     *     summary="Send read receipt",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_ids"},
     *             @OA\Property(property="message_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Read receipt sent successfully"
     *     )
     * )
     */
    public function sendReadReceipt(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $messageIds = $request->input('message_ids');

        $readReceipts = $this->rtmService->sendReadReceipt($user->id, $messageIds);

        // Broadcast read receipts via Laravel broadcasting for fallback
        foreach ($readReceipts as $receipt) {
            broadcast(new RtmReadReceipt(
                $user->id,
                $receipt['sender_id'],
                $messageIds,
                $receipt['read_at']
            ))->toOthers();
        }

        return $this->successResponse($readReceipts, 'Read receipt sent successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/rtm/offline-messages",
     *     summary="Get offline messages",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Offline messages retrieved successfully"
     *     )
     * )
     */
    public function getOfflineMessages(Request $request): JsonResponse
    {
        $user = $request->user();
        $offlineMessages = $this->rtmService->getOfflineMessages($user->id);

        return $this->successResponse($offlineMessages, 'Offline messages retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/rtm/user-status",
     *     summary="Update user online status",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_online"},
     *             @OA\Property(property="is_online", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully"
     *     )
     * )
     */
    public function updateUserStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_online' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $userStatus = $this->rtmService->updateUserStatus($user->id, $request->input('is_online'));

        return $this->successResponse($userStatus, 'User status updated successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/rtm/user-status/{userId}",
     *     summary="Get user online status",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User status retrieved successfully"
     *     )
     * )
     */
    public function getUserStatus(Request $request, $userId): JsonResponse
    {
        $validator = Validator::make(['userId' => $userId], [
            'userId' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $userStatus = $this->rtmService->getUserStatus((int) $userId);

        return $this->successResponse($userStatus, 'User status retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/rtm/refresh-token",
     *     summary="Refresh RTM token",
     *     tags={"RTM Messaging"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="RTM token refreshed successfully"
     *     )
     * )
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Invalidate old token
        $this->agoraService->invalidateRtmToken($user->id);
        
        // Generate new token
        $newConfig = $this->agoraService->getRtmConfig($user->id);

        return $this->successResponse($newConfig, 'RTM token refreshed successfully');
    }
}
