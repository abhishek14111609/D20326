<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Swipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RtmService
{
    protected $agoraService;
    protected $chatService;
    protected $messageCacheService;
    protected $offlineMessageService;

    public function __construct(
        AgoraService $agoraService, 
        ChatService $chatService,
        MessageCacheService $messageCacheService,
        OfflineMessageService $offlineMessageService
    ) {
        $this->agoraService = $agoraService;
        $this->chatService = $chatService;
        $this->messageCacheService = $messageCacheService;
        $this->offlineMessageService = $offlineMessageService;
    }

    /**
     * Send a real-time message via RTM
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $message
     * @param string $messageType
     * @param array $metadata
     * @return array
     */
    public function sendRtmMessage($senderId, $receiverId, $message, $messageType = 'text', $metadata = [])
    {
        try {
            // Validate match
            $this->validateMatch($senderId, $receiverId);

            // Create message in database
            $messageModel = $this->createMessage($senderId, $receiverId, $message, $messageType, $metadata);

            // Update conversation
            $conversation = $this->getOrCreateConversation($senderId, $receiverId);
            $conversation->updateLastMessage($messageModel);

            // Cache the message for instant delivery
            $this->messageCacheService->addMessageToCache($conversation->id, [
                'id' => $messageModel->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $messageModel->sender_id,
                'receiver_id' => $messageModel->receiver_id,
                'message' => $messageModel->message,
                'type' => $messageModel->type,
                'media_path' => $messageModel->media_path,
                'media_type' => $messageModel->media_type,
                'read_at' => $messageModel->read_at,
                'created_at' => $messageModel->created_at->toISOString(),
                'updated_at' => $messageModel->updated_at->toISOString(),
                'sender' => [
                    'id' => $messageModel->sender->id,
                    'name' => $messageModel->sender->name,
                    'profile_image' => $messageModel->sender->profile_image
                ]
            ]);

            // Prepare RTM message payload
            $rtmPayload = [
                'type' => 'message',
                'id' => $messageModel->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $message,
                'message_type' => $messageType,
                'conversation_id' => $conversation->id,
                'timestamp' => $messageModel->created_at->timestamp,
                'metadata' => $metadata,
            ];

            // Store message for offline delivery
            $this->offlineMessageService->storeOfflineMessage($receiverId, $rtmPayload);

            // Return payload for immediate client-side display
            return [
                'success' => true,
                'message_id' => $messageModel->id,
                'conversation_id' => $conversation->id,
                'rtm_payload' => $rtmPayload,
                'channel' => $this->agoraService->generateConversationChannel($senderId, $receiverId),
            ];

        } catch (\Exception $e) {
            Log::error('RTM message send failed', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create message in database
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $message
     * @param string $messageType
     * @param array $metadata
     * @return Message
     */
    protected function createMessage($senderId, $receiverId, $message, $messageType, $metadata = [])
    {
        $conversation = $this->getOrCreateConversation($senderId, $receiverId);

        return Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'type' => $messageType,
            'media_path' => $metadata['media_path'] ?? null,
            'media_type' => $metadata['media_type'] ?? null,
        ]);
    }

    /**
     * Get or create conversation between two users
     *
     * @param int $userId1
     * @param int $userId2
     * @return Conversation
     */
    public function getOrCreateConversation($userId1, $userId2)
    {
        // Sort user IDs for consistent conversation lookup
        $users = [$userId1, $userId2];
        sort($users);

        $conversation = Conversation::betweenUsers($users[0], $users[1])->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user1_id' => $users[0],
                'user2_id' => $users[1],
                'unread_count_user1' => 0,
                'unread_count_user2' => 0,
            ]);
        }

        return $conversation;
    }

    /**
     * Validate if two users are matched
     *
     * @param int $userId1
     * @param int $userId2
     * @throws \Exception
     */
    protected function validateMatch($userId1, $userId2)
    {
        $match = Swipe::where(function($query) use ($userId1, $userId2) {
            $query->where('swiper_id', $userId1)
                  ->where('swiped_id', $userId2)
                  ->where('type', 'like')
                  ->where('matched', true);
        })->orWhere(function($query) use ($userId1, $userId2) {
            $query->where('swiper_id', $userId2)
                  ->where('swiped_id', $userId1)
                  ->where('type', 'like')
                  ->where('matched', true);
        })->exists();

        if (!$match) {
            throw new \Exception('Users must be matched to send messages');
        }
    }


    /**
     * Get offline messages for user
     *
     * @param int $userId
     * @return array
     */
    public function getOfflineMessages($userId)
    {
        return $this->offlineMessageService->getOfflineMessages($userId);
    }

    /**
     * Send typing indicator
     *
     * @param int $senderId
     * @param int $receiverId
     * @param bool $isTyping
     * @return array
     */
    public function sendTypingIndicator($senderId, $receiverId, $isTyping = true)
    {
        return [
            'type' => 'typing',
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'is_typing' => $isTyping,
            'timestamp' => now()->timestamp,
            'channel' => $this->agoraService->generateConversationChannel($senderId, $receiverId),
        ];
    }

    /**
     * Send message read receipt
     *
     * @param int $userId
     * @param array $messageIds
     * @return array
     */
    public function sendReadReceipt($userId, $messageIds)
    {
        // Mark messages as read in database
        Message::whereIn('id', $messageIds)
               ->where('receiver_id', $userId)
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        // Get sender IDs for read receipts
        $senders = Message::whereIn('id', $messageIds)
                         ->where('receiver_id', $userId)
                         ->pluck('sender_id')
                         ->unique();

        $receipts = [];
        foreach ($senders as $senderId) {
            $receipts[] = [
                'type' => 'read_receipt',
                'receiver_id' => $userId,
                'sender_id' => $senderId,
                'message_ids' => $messageIds,
                'read_at' => now()->timestamp,
                'channel' => $this->agoraService->generateConversationChannel($userId, $senderId),
            ];
        }

        return $receipts;
    }

    /**
     * Get conversation messages with pagination
     *
     * @param int $userId
     * @param int $otherUserId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getConversationMessages($userId, $otherUserId, $page = 1, $perPage = 50)
    {
        $conversation = $this->getOrCreateConversation($userId, $otherUserId);

        // Try to get cached messages first
        if ($page === 1) {
            $cachedMessages = $this->messageCacheService->getCachedMessages($conversation->id, $perPage);
            if (!empty($cachedMessages)) {
                return [
                    'conversation_id' => $conversation->id,
                    'messages' => $cachedMessages,
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => count($cachedMessages),
                        'total' => count($cachedMessages),
                        'from_cache' => true
                    ],
                ];
            }
        }

        // Fallback to database query
        $messages = Message::where('conversation_id', $conversation->id)
                          ->with(['sender:id,name,profile_image'])
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);

        // Cache the messages for future requests
        if ($page === 1) {
            $this->messageCacheService->cacheConversationMessages(
                $conversation->id, 
                $messages->items()
            );
        }

        return [
            'conversation_id' => $conversation->id,
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'from_cache' => false
            ],
        ];
    }

    /**
     * Get user's conversations
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUserConversations($userId, $page = 1, $perPage = 20)
    {
        // Try to get cached conversations first
        if ($page === 1) {
            $cachedConversations = $this->messageCacheService->getCachedUserConversations($userId);
            if ($cachedConversations) {
                return [
                    'conversations' => array_slice($cachedConversations, 0, $perPage),
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => ceil(count($cachedConversations) / $perPage),
                        'per_page' => $perPage,
                        'total' => count($cachedConversations),
                        'from_cache' => true
                    ],
                ];
            }
        }

        // Fallback to database query
        $conversations = Conversation::forUser($userId)
                                   ->with(['user1:id,name,profile_image', 'user2:id,name,profile_image', 'lastMessage'])
                                   ->orderBy('updated_at', 'desc')
                                   ->paginate($perPage, ['*'], 'page', $page);

        // Cache conversations for future requests
        if ($page === 1) {
            $this->messageCacheService->cacheUserConversations($userId, $conversations->items());
        }

        return [
            'conversations' => $conversations->items(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
                'from_cache' => false
            ],
        ];
    }

    /**
     * Update user online status
     *
     * @param int $userId
     * @param bool $isOnline
     * @return array
     */
    public function updateUserStatus($userId, $isOnline = true)
    {
        $status = [
            'user_id' => $userId,
            'is_online' => $isOnline,
            'last_seen' => now()->timestamp,
        ];

        // Cache user status
        $this->messageCacheService->cacheUserStatus($userId, $isOnline, now()->timestamp);

        return [
            'type' => 'user_status',
            'payload' => $status,
            'channel' => $this->agoraService->generatePresenceChannel($userId),
        ];
    }

    /**
     * Get user online status
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserStatus($userId)
    {
        return $this->messageCacheService->getCachedUserStatus($userId);
    }
}
