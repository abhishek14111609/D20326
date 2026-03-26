<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\MessageCollection;
use App\Http\Resources\Api\ConversationCollection;
use App\Http\Resources\Api\MessageResource;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;
use App\Events\MessageSent as MessageSentEvent;
use App\Events\UserTyping;
use App\Events\MessageRead as MessageReadEvent;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Str;
use App\Models\Swipe;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="Chat functionality for matched users"
 * )
 */
class ChatController extends BaseController
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/chat/conversations",
     *     summary="Get user's conversations",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user's conversations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Conversation")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=0),
     *                 @OA\Property(property="count", type="integer", example=0),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function conversations(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = 10;
        $conversations = $this->chatService->getUserConversations(auth()->id(), $page, $perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => new ConversationCollection($conversations->items()),
            'pagination' => [
                'total' => $conversations->total(),
                'count' => $conversations->count(),
                'total_pages' => ceil($conversations->total() / $perPage),
                'current_page' => $conversations->currentPage(),
                'from' => $conversations->firstItem() ?? 0,
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'to' => $conversations->lastItem() ?? 0,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/chat/conversation/{user}",
     *     summary="Get messages in a conversation",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the other user in the conversation",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages in the conversation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Message")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/v1/chat/conversation/1"),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not matched with this user",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function getMessages($userId, Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        
        $messages = $this->chatService->getConversation(
            auth()->id(),
            $userId,
            $page,
            $perPage
        );
        
        // Mark messages as read
        $this->chatService->markAsRead(auth()->id(), $userId);

        return response()->json([
            'status' => 'success',
            'data' => new MessageCollection($messages->items()),
            'pagination' => [
                'total' => $messages->total(),
                'count' => $messages->count(),
                'total_pages' => ceil($messages->total() / $perPage),
                'current_page' => $messages->currentPage(),
                'from' => $messages->firstItem() ?? 0,
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'to' => $messages->lastItem() ?? 0,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/chat/send/{user}",
     *     summary="Send a message to a user",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the recipient user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Hello, how are you?")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not matched with this user or conversation not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
public function sendMessage($userId, Request $request)
{
    $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    $sender = $request->user();
    $receiver = User::findOrFail($userId);

    try {
        // Check if matched
        $match = Swipe::where(function($query) use ($sender, $userId) {
            $query->where('swiper_id', $sender->id)
                  ->where('swiped_id', $userId)
                  ->where('type', 'like');
                  //->where('matched', true);
        })->orWhere(function($query) use ($sender, $userId) {
            $query->where('swiper_id', $userId)
                  ->where('swiped_id', $sender->id)
                  ->where('type', 'like');
                  //->where('matched', true);
        })->exists();

        //if (!$match) {
            //return $this->errorResponse('You can only message matched users', 403);
        //}

        // Save Message
        $message = $this->chatService->sendMessage(
            $sender->id,
            $receiver->id,
            $request->input('message')
        );

        // Save Notification in DB
        $notification = Notification::create([
            'user_id' => $receiver->id,
            'type' => Notification::TYPE_SYSTEM,
            'message' => 'New message from ' . $sender->name,
            'data' => [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'preview' => Str::limit($message->message, 100),
                'notification_type' => 'chat_message'
            ]
        ]);

        // Send Push
        $this->sendChatNotification($receiver, $notification, $sender, $message);

        return $this->successResponse(
            new MessageResource($message),
            'Message sent successfully'
        );

    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

	
protected function sendChatNotification(User $receiver, Notification $notification, User $sender, $message)
{
    try {
        $fcmToken = $receiver->fcm_token
            ?? $receiver->fcmTokens()->latest()->first()?->token;

        if (!$fcmToken) {
            Log::info("No FCM token for chat", ['user_id' => $receiver->id]);
            return;
        }

        $fcmService = app(\App\Services\FcmService::class);

        $title = "New message from {$sender->name}";
        $body  = Str::limit($message->message, 50);

        // SAME working payload (profile update जैसा)
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
                    "type" => "chat_message",
                    "notification_id" => (string)$notification->id,
                    "message_id" => (string)$notification->data['message_id'],
                    "sender_id" => (string)$sender->id,
                    "sender_name" => $sender->name,
                    "preview" => $notification->data['preview'],
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ]
            ]
        ];

        // Send to FCM service
        $fcmService->sendNotification(
            $fcmToken,
            $title,
            $body,
            $payload["message"]["data"]
        );

        Log::info("CHAT FCM SENT", [
            'receiver_id' => $receiver->id,
            'fcm_token' => substr($fcmToken, 0, 10) . '...'
        ]);

    } catch (\Exception $e) {
        Log::error("Chat FCM failed", ['error' => $e->getMessage()]);
    }
}


    /**
     * @OA\Post(
     *     path="/api/chat/send-image/{user}",
     *     summary="Send an image message to a user",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the recipient user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload (max: 5MB, allowed types: jpg,jpeg,png,gif)"
     *                 ),
     *                 @OA\Property(
     *                     property="caption",
     *                     type="string",
     *                     description="Optional caption for the image",
     *                     maxLength=255
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Image sent successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not matched with this user or conversation not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function sendImage($userId, Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'caption' => 'nullable|string|max:255',
        ]);

        try {
            $imagePath = $request->file('image')->store('chat/images', 'public');
            
            $message = $this->chatService->sendImageMessage(
                auth()->id(),
                $userId,
                $imagePath,
                $request->input('caption')
            );
            
            // Broadcast the image message to the recipient
            broadcast(new MessageSentEvent($message, $userId))->toOthers();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Image sent successfully',
                'data' => new MessageResource($message)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], (int)$e->getCode() ?: 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/chat/read/{user}",
     *     summary="Mark messages as read",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user whose messages to mark as read",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Messages marked as read")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to mark messages as read",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * Mark all unread messages from a specific user as read
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($userId)
    {
        try {
            $currentUser = auth()->user();
            $updated = $this->chatService->markAsRead($currentUser->id, $userId);
            
            // Broadcast read receipt to the sender
            broadcast(new MessageReadEvent($currentUser->id, $userId));

            return response()->json([
                'status' => 'success',
                'message' => $updated > 0 ? 'Messages marked as read' : 'No unread messages found',
                'data' => [
                    'updated_count' => $updated,
                    'unread_count' => $this->chatService->getUnreadCount($currentUser->id)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark messages as read',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], (int)$e->getCode() ?: 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/chat/typing/{user}",
     *     summary="Update typing status",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update typing status for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_typing"},
     *             @OA\Property(property="is_typing", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Typing status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Typing status updated")
     *         )
     *     )
     * )
     */
    public function typing($userId, Request $request)
    {
        $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        $typingUser = auth()->user();
        
        broadcast(new UserTyping(
            $typingUser->id,
            $userId,
            $request->is_typing
        ))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Typing status updated'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/chat/{chatId}",
     *     summary="Get chat details by ID",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="chatId",
     *         in="path",
     *         required=true,
     *         description="ID of the chat to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized to access this chat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Not authorized to access this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found")
     *         )
     *     )
     * )
     *
     * Get chat details by ID
     *
     * @param int $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatDetails($chatId)
    {
        try {
            $user = auth()->user();
            
            // Get chat with related data (messages, participants, etc.)
            $chat = $this->chatService->getChatWithDetails($chatId, $user->id);
            
            if (!$chat) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chat not found or access denied'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'type' => $chat->type,
                    'created_at' => $chat->created_at,
                    'updated_at' => $chat->updated_at,
                    'unread_count' => $chat->unread_count ?? 0,
                    'last_message' => $chat->lastMessage,
                    'participants' => $chat->participants->map(function($participant) use ($user) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'avatar' => $participant->profile_photo_url,
                            'is_online' => $participant->is_online ?? false,
                            'is_you' => $participant->id === $user->id
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch chat details: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch chat details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], (int)$e->getCode() ?: 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/chat/message/{message}",
     *     summary="Delete a message",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         required=true,
     *         description="ID of the message to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete this message",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * Delete a message
     *
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMessage($messageId)
    {
        try {
            $user = auth()->user();
            
            // Find the message
            $message = \App\Models\Chat::find($messageId);
            
            if (!$message) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Message not found'
                ], 404);
            }
            
            // Check if the user is the sender of the message
            if ($message->sender_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this message'
                ], 403);
            }
            
            // Delete the message
            $message->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Message deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to delete message: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], (int)$e->getCode() ?: 500);
        }
    }
}
