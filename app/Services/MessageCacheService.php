<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MessageCacheService
{
    protected $cachePrefix = 'chat_messages';
    protected $conversationPrefix = 'chat_conversations';
    protected $userStatusPrefix = 'user_status';

    /**
     * Cache recent messages for a conversation
     *
     * @param int $conversationId
     * @param array $messages
     * @param int $ttl
     */
    public function cacheConversationMessages($conversationId, $messages, $ttl = 3600)
    {
        $cacheKey = "{$this->cachePrefix}:conversation:{$conversationId}";
        
        // Store messages in Redis with TTL
        Redis::setex($cacheKey, $ttl, json_encode($messages));
        
        // Also cache individual message IDs for quick lookup
        $messageIds = collect($messages)->pluck('id')->toArray();
        Redis::setex("{$cacheKey}:ids", $ttl, json_encode($messageIds));
    }

    /**
     * Get cached messages for a conversation
     *
     * @param int $conversationId
     * @param int $limit
     * @return array
     */
    public function getCachedMessages($conversationId, $limit = 50)
    {
        $cacheKey = "{$this->cachePrefix}:conversation:{$conversationId}";
        $cachedMessages = Redis::get($cacheKey);
        
        if ($cachedMessages) {
            $messages = json_decode($cachedMessages, true);
            return array_slice($messages, -$limit);
        }
        
        return [];
    }

    /**
     * Add a new message to cache
     *
     * @param int $conversationId
     * @param array $message
     */
    public function addMessageToCache($conversationId, $message)
    {
        $cacheKey = "{$this->cachePrefix}:conversation:{$conversationId}";
        $existingMessages = $this->getCachedMessages($conversationId, 1000);
        
        // Add new message to the end
        $existingMessages[] = $message;
        
        // Keep only last 100 messages in cache
        if (count($existingMessages) > 100) {
            $existingMessages = array_slice($existingMessages, -100);
        }
        
        // Update cache
        Redis::setex($cacheKey, 3600, json_encode($existingMessages));
        
        // Update message IDs cache
        $messageIds = collect($existingMessages)->pluck('id')->toArray();
        Redis::setex("{$cacheKey}:ids", 3600, json_encode($messageIds));
    }

    /**
     * Update message status in cache
     *
     * @param int $messageId
     * @param string $status
     */
    public function updateMessageStatus($messageId, $status)
    {
        $cacheKey = "{$this->cachePrefix}:status:{$messageId}";
        Redis::setex($cacheKey, 86400, $status); // 24 hours
    }

    /**
     * Get message status from cache
     *
     * @param int $messageId
     * @return string|null
     */
    public function getMessageStatus($messageId)
    {
        $cacheKey = "{$this->cachePrefix}:status:{$messageId}";
        return Redis::get($cacheKey);
    }

    /**
     * Cache conversation metadata
     *
     * @param int $conversationId
     * @param array $metadata
     */
    public function cacheConversationMetadata($conversationId, $metadata)
    {
        $cacheKey = "{$this->conversationPrefix}:meta:{$conversationId}";
        Redis::setex($cacheKey, 1800, json_encode($metadata)); // 30 minutes
    }

    /**
     * Get cached conversation metadata
     *
     * @param int $conversationId
     * @return array|null
     */
    public function getCachedConversationMetadata($conversationId)
    {
        $cacheKey = "{$this->conversationPrefix}:meta:{$conversationId}";
        $cached = Redis::get($cacheKey);
        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Cache user conversations list
     *
     * @param int $userId
     * @param array $conversations
     */
    public function cacheUserConversations($userId, $conversations)
    {
        $cacheKey = "{$this->conversationPrefix}:user:{$userId}";
        Redis::setex($cacheKey, 600, json_encode($conversations)); // 10 minutes
    }

    /**
     * Get cached user conversations
     *
     * @param int $userId
     * @return array|null
     */
    public function getCachedUserConversations($userId)
    {
        $cacheKey = "{$this->conversationPrefix}:user:{$userId}";
        $cached = Redis::get($cacheKey);
        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Update conversation in user's cache
     *
     * @param int $userId
     * @param array $conversation
     */
    public function updateConversationInCache($userId, $conversation)
    {
        $cacheKey = "{$this->conversationPrefix}:user:{$userId}";
        $conversations = $this->getCachedUserConversations($userId) ?? [];
        
        // Update or add conversation
        $updated = false;
        foreach ($conversations as &$conv) {
            if ($conv['id'] === $conversation['id']) {
                $conv = $conversation;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            array_unshift($conversations, $conversation);
        }
        
        // Sort by last_message_at
        usort($conversations, function($a, $b) {
            return strtotime($b['last_message_at']) - strtotime($a['last_message_at']);
        });
        
        Redis::setex($cacheKey, 600, json_encode($conversations));
    }

    /**
     * Cache user online status
     *
     * @param int $userId
     * @param bool $isOnline
     * @param int $lastSeen
     */
    public function cacheUserStatus($userId, $isOnline, $lastSeen = null)
    {
        $cacheKey = "{$this->userStatusPrefix}:{$userId}";
        $status = [
            'user_id' => $userId,
            'is_online' => $isOnline,
            'last_seen' => $lastSeen ?? time(),
            'cached_at' => time()
        ];
        
        $ttl = $isOnline ? 300 : 86400; // 5 minutes if online, 24 hours if offline
        Redis::setex($cacheKey, $ttl, json_encode($status));
    }

    /**
     * Get cached user status
     *
     * @param int $userId
     * @return array|null
     */
    public function getCachedUserStatus($userId)
    {
        $cacheKey = "{$this->userStatusPrefix}:{$userId}";
        $cached = Redis::get($cacheKey);
        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Get multiple user statuses
     *
     * @param array $userIds
     * @return array
     */
    public function getCachedUserStatuses($userIds)
    {
        $statuses = [];
        foreach ($userIds as $userId) {
            $status = $this->getCachedUserStatus($userId);
            if ($status) {
                $statuses[$userId] = $status;
            }
        }
        return $statuses;
    }

    /**
     * Cache message delivery status
     *
     * @param int $messageId
     * @param int $userId
     * @param string $status
     */
    public function cacheMessageDelivery($messageId, $userId, $status)
    {
        $cacheKey = "{$this->cachePrefix}:delivery:{$messageId}:{$userId}";
        Redis::setex($cacheKey, 86400, $status);
    }

    /**
     * Get message delivery status
     *
     * @param int $messageId
     * @param int $userId
     * @return string|null
     */
    public function getMessageDeliveryStatus($messageId, $userId)
    {
        $cacheKey = "{$this->cachePrefix}:delivery:{$messageId}:{$userId}";
        return Redis::get($cacheKey);
    }

    /**
     * Invalidate conversation cache
     *
     * @param int $conversationId
     */
    public function invalidateConversationCache($conversationId)
    {
        $keys = [
            "{$this->cachePrefix}:conversation:{$conversationId}",
            "{$this->cachePrefix}:conversation:{$conversationId}:ids",
            "{$this->conversationPrefix}:meta:{$conversationId}"
        ];
        
        foreach ($keys as $key) {
            Redis::del($key);
        }
    }

    /**
     * Invalidate user conversations cache
     *
     * @param int $userId
     */
    public function invalidateUserConversationsCache($userId)
    {
        $cacheKey = "{$this->conversationPrefix}:user:{$userId}";
        Redis::del($cacheKey);
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getCacheStats()
    {
        $stats = [
            'total_keys' => 0,
            'memory_usage' => 0,
            'hit_rate' => 0
        ];

        try {
            $info = Redis::info();
            $stats['total_keys'] = $info['db0']['keys'] ?? 0;
            $stats['memory_usage'] = $info['used_memory_human'] ?? '0B';
            $stats['hit_rate'] = $info['keyspace_hits'] / max($info['keyspace_hits'] + $info['keyspace_misses'], 1) * 100;
        } catch (\Exception $e) {
            // Redis not available
        }

        return $stats;
    }

    /**
     * Warm up cache with recent conversations
     *
     * @param int $userId
     * @param int $limit
     */
    public function warmUpCache($userId, $limit = 20)
    {
        $conversations = Conversation::forUser($userId)
            ->with(['user1', 'user2', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($conversations as $conversation) {
            // Cache conversation metadata
            $this->cacheConversationMetadata($conversation->id, [
                'id' => $conversation->id,
                'user1_id' => $conversation->user1_id,
                'user2_id' => $conversation->user2_id,
                'last_message_id' => $conversation->last_message_id,
                'last_message_at' => $conversation->last_message_at,
                'unread_count_user1' => $conversation->unread_count_user1,
                'unread_count_user2' => $conversation->unread_count_user2,
            ]);

            // Cache recent messages
            $messages = Message::where('conversation_id', $conversation->id)
                ->with(['sender:id,name,profile_image'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values()
                ->toArray();

            if (!empty($messages)) {
                $this->cacheConversationMessages($conversation->id, $messages);
            }
        }
    }
}
