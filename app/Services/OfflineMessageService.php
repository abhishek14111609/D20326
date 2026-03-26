<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class OfflineMessageService
{
    protected $cachePrefix = 'offline_messages';
    protected $maxOfflineMessages = 100;
    protected $offlineMessageTtl = 86400; // 24 hours

    /**
     * Store message for offline delivery
     *
     * @param int $userId
     * @param array $message
     * @return void
     */
    public function storeOfflineMessage($userId, $message)
    {
        try {
            $cacheKey = "{$this->cachePrefix}:{$userId}";
            $offlineMessages = Cache::get($cacheKey, []);

            // Add message to offline queue
            $offlineMessages[] = [
                'message' => $message,
                'stored_at' => now()->timestamp,
                'attempts' => 0,
            ];

            // Keep only recent messages
            if (count($offlineMessages) > $this->maxOfflineMessages) {
                $offlineMessages = array_slice($offlineMessages, -$this->maxOfflineMessages);
            }

            // Store in cache with TTL
            Cache::put($cacheKey, $offlineMessages, $this->offlineMessageTtl);

            // Also store in database for persistence
            $this->storeInDatabase($userId, $message);

            Log::info('Offline message stored', [
                'user_id' => $userId,
                'message_id' => $message['id'] ?? null,
                'total_offline' => count($offlineMessages)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store offline message', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
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
        try {
            $cacheKey = "{$this->cachePrefix}:{$userId}";
            $offlineMessages = Cache::get($cacheKey, []);

            // Also get from database as fallback
            $dbMessages = $this->getFromDatabase($userId);
            
            // Merge and deduplicate
            $allMessages = $this->mergeOfflineMessages($offlineMessages, $dbMessages);

            // Clear cache after retrieval
            Cache::forget($cacheKey);

            // Clear database messages after retrieval
            $this->clearDatabaseMessages($userId);

            Log::info('Offline messages retrieved', [
                'user_id' => $userId,
                'count' => count($allMessages)
            ]);

            return $allMessages;

        } catch (\Exception $e) {
            Log::error('Failed to get offline messages', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Check if user has offline messages
     *
     * @param int $userId
     * @return bool
     */
    public function hasOfflineMessages($userId)
    {
        $cacheKey = "{$this->cachePrefix}:{$userId}";
        $cacheMessages = Cache::get($cacheKey, []);
        
        $dbMessages = $this->getFromDatabase($userId);
        
        return !empty($cacheMessages) || !empty($dbMessages);
    }

    /**
     * Get offline message count for user
     *
     * @param int $userId
     * @return int
     */
    public function getOfflineMessageCount($userId)
    {
        $cacheKey = "{$this->cachePrefix}:{$userId}";
        $cacheMessages = Cache::get($cacheKey, []);
        
        $dbMessages = $this->getFromDatabase($userId);
        
        return count($cacheMessages) + count($dbMessages);
    }

    /**
     * Store message in database for persistence
     *
     * @param int $userId
     * @param array $message
     */
    protected function storeInDatabase($userId, $message)
    {
        try {
            $data = [
                'user_id' => $userId,
                'message_data' => json_encode($message),
                'stored_at' => now(),
                'delivered' => false,
                'attempts' => 0,
            ];

            \DB::table('offline_messages')->insert($data);

        } catch (\Exception $e) {
            Log::error('Failed to store offline message in database', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get offline messages from database
     *
     * @param int $userId
     * @return array
     */
    protected function getFromDatabase($userId)
    {
        try {
            $messages = \DB::table('offline_messages')
                ->where('user_id', $userId)
                ->where('delivered', false)
                ->orderBy('stored_at', 'asc')
                ->limit($this->maxOfflineMessages)
                ->get();

            return $messages->map(function ($message) {
                return [
                    'message' => json_decode($message->message_data, true),
                    'stored_at' => $message->stored_at,
                    'attempts' => $message->attempts,
                    'id' => $message->id,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to get offline messages from database', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Clear offline messages from database
     *
     * @param int $userId
     */
    protected function clearDatabaseMessages($userId)
    {
        try {
            \DB::table('offline_messages')
                ->where('user_id', $userId)
                ->where('delivered', false)
                ->update([
                    'delivered' => true,
                    'delivered_at' => now()
                ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear offline messages from database', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Merge offline messages from cache and database
     *
     * @param array $cacheMessages
     * @param array $dbMessages
     * @return array
     */
    protected function mergeOfflineMessages($cacheMessages, $dbMessages)
    {
        $allMessages = array_merge($cacheMessages, $dbMessages);
        
        // Deduplicate by message ID
        $uniqueMessages = [];
        $seenIds = [];

        foreach ($allMessages as $messageData) {
            $messageId = $messageData['message']['id'] ?? null;
            if ($messageId && !in_array($messageId, $seenIds)) {
                $seenIds[] = $messageId;
                $uniqueMessages[] = $messageData;
            }
        }

        // Sort by stored_at timestamp
        usort($uniqueMessages, function ($a, $b) {
            return $a['stored_at'] <=> $b['stored_at'];
        });

        return $uniqueMessages;
    }

    /**
     * Clean up old offline messages
     *
     * @param int $daysOld
     */
    public function cleanupOldMessages($daysOld = 7)
    {
        try {
            $cutoffDate = now()->subDays($daysOld);

            // Clean cache (it will expire automatically)
            
            // Clean database
            \DB::table('offline_messages')
                ->where('stored_at', '<', $cutoffDate)
                ->delete();

            Log::info('Cleaned up old offline messages', [
                'cutoff_date' => $cutoffDate,
                'days_old' => $daysOld
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old offline messages', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get offline message statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        try {
            $totalOffline = \DB::table('offline_messages')
                ->where('delivered', false)
                ->count();

            $totalDelivered = \DB::table('offline_messages')
                ->where('delivered', true)
                ->count();

            $oldestMessage = \DB::table('offline_messages')
                ->where('delivered', false)
                ->orderBy('stored_at', 'asc')
                ->first();

            return [
                'total_offline' => $totalOffline,
                'total_delivered' => $totalDelivered,
                'oldest_message_date' => $oldestMessage ? $oldestMessage->stored_at : null,
                'cache_keys' => $this->getCacheKeys(),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get offline message statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_offline' => 0,
                'total_delivered' => 0,
                'oldest_message_date' => null,
                'cache_keys' => [],
            ];
        }
    }

    /**
     * Get all cache keys for offline messages
     *
     * @return array
     */
    protected function getCacheKeys()
    {
        try {
            // This is Redis-specific, adjust for your cache driver
            $pattern = "{$this->cachePrefix}:*";
            $keys = \Redis::keys($pattern);
            return array_map(function ($key) {
                return str_replace(config('cache.prefix') . ':', '', $key);
            }, $keys);

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Store multiple messages for offline delivery
     *
     * @param int $userId
     * @param array $messages
     */
    public function storeMultipleOfflineMessages($userId, $messages)
    {
        foreach ($messages as $message) {
            $this->storeOfflineMessage($userId, $message);
        }
    }

    /**
     * Mark messages as delivered (for tracking purposes)
     *
     * @param int $userId
     * @param array $messageIds
     */
    public function markMessagesAsDelivered($userId, $messageIds)
    {
        try {
            \DB::table('offline_messages')
                ->where('user_id', $userId)
                ->whereIn('message_data->id', $messageIds)
                ->update([
                    'delivered' => true,
                    'delivered_at' => now()
                ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark messages as delivered', [
                'user_id' => $userId,
                'message_ids' => $messageIds,
                'error' => $e->getMessage()
            ]);
        }
    }
}
