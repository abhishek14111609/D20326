<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;
use App\Models\Swipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ChatService
{
    /**
     * Get all conversations for a user with pagination
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserConversations($userId, $page = 1, $perPage = 10)
    {
        // First, get the latest message ID for each conversation
        $latestMessageIds = DB::table('chats')
            ->select(DB::raw('MAX(id) as id'))
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->groupBy(DB::raw('CASE WHEN sender_id = '.$userId.' THEN receiver_id ELSE sender_id END'));

        // Then get the full message details for those IDs
        $latestMessages = Chat::whereIn('id', $latestMessageIds)
            ->get()
            ->keyBy(function($message) use ($userId) {
                return $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;
            });

        // Get all unique user IDs from the latest messages
        $conversationUserIds = $latestMessages->map(function($message) use ($userId) {
            return $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;
        })->unique()->values();

        if ($conversationUserIds->isEmpty()) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }

        // Get user details with latest message
        $users = \App\Models\User::whereIn('id', $conversationUserIds)
            ->with(['profile'])
            ->get()
            ->map(function($user) use ($latestMessages, $userId) {
                $latestMessage = $latestMessages[$user->id] ?? null;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->profile->profile_photo_url ?? null,
                    'is_online' => $user->is_online ?? false,
                    'latest_message' => $latestMessage ? [
                        'id' => $latestMessage->id,
                        'message' => $latestMessage->message,
                        'message_type' => $latestMessage->message_type,
                        'is_read' => (bool) $latestMessage->is_read,
                        'created_at' => $latestMessage->created_at->toDateTimeString(),
                    ] : null,
                    'unread_count' => $this->getUnreadCountFromUser($userId, $user->id),
                    'created_at' => $latestMessage ? $latestMessage->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'updated_at' => $latestMessage ? $latestMessage->created_at->toDateTimeString() : now()->toDateTimeString(),
                ];
            });

        // Sort by latest message time
        $sortedUsers = $users->sortByDesc(function($user) {
            return $user['latest_message']['created_at'] ?? $user['created_at'];
        });

        // Paginate the results
        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $perPage = $perPage ?: 10;
        $items = $sortedUsers->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $sortedUsers->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    /**
     * Get unread message count from a specific user
     */
    protected function getUnreadCountFromUser($currentUserId, $otherUserId)
    {
        return Chat::where('sender_id', $otherUserId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get conversation between two users with pagination
     *
     * @param int $userId
     * @param int $otherUserId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getConversation($userId, $otherUserId, $page = 1, $perPage = 20)
    {
        // Check if users are matched
        $this->validateMatch($userId, $otherUserId);

        // Get messages between the two users using the Chat model
        return Chat::where(function($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $userId);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Send a message from one user to another
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $message
     * @param string $messageType
     * @return \App\Models\Chat
     */
    public function sendMessage($senderId, $receiverId, $message, $messageType = 'text')
    {
        return Chat::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'message_type' => $messageType,
            'is_read' => false, // This will be cast to the correct type by the model
            'sent_at' => now(),
        ]);
    }

    /**
     * Send an image message
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $imagePath
     * @param string|null $caption
     * @return \App\Models\Chat
     */
    public function sendImageMessage($senderId, $receiverId, $imagePath, $caption = null)
    {
        // Check if users are matched
        $this->validateMatch($senderId, $receiverId);

        return DB::transaction(function () use ($senderId, $receiverId, $imagePath, $caption) {
            // Create the message with image
            return Chat::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $caption,
                'media_path' => $imagePath,
                'media_type' => 'image',
                'message_type' => 'media',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        });
    }

    /**
     * Validate if two users are matched
     *
     * @param int $userId1
     * @param int $userId2
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @return void
     */
    protected function validateMatch($userId1, $userId2)
{
    // Check if user1 has liked user2
    $user1LikedUser2 = Swipe::where('swiper_id', $userId1)
        ->where('swiped_id', $userId2)
        ->where('type', 'like')
        ->exists();

    // Check if user2 has liked user1
    $user2LikedUser1 = Swipe::where('swiper_id', $userId2)
        ->where('swiped_id', $userId1)
        ->where('type', 'like')
        ->exists();

    if (!$user1LikedUser2 && !$user2LikedUser1) {
        return response()->json([
            'status' => 'error',
            'message' => 'Match required to start chatting',
            'data' => [
                'can_message' => false,
                'action_required' => 'like',
                'hint' => 'Like this profile to show your interest. You can chat if they like you back.'
            ]
        ], 403);
    } 
    
    if (!$user1LikedUser2) {
        return response()->json([
            'status' => 'error',
            'message' => 'Like required',
            'data' => [
                'can_message' => false,
                'action_required' => 'like',
                'hint' => 'Like this profile to show your interest. You can chat if they like you back.'
            ]
        ], 403);
    } 
    
    if (!$user2LikedUser1) {
        return response()->json([
            'status' => 'error',
            'message' => 'Waiting for match',
            'data' => [
                'can_message' => false,
                'action_required' => 'wait',
                'hint' => 'This user hasn\'t liked you back yet. Keep exploring!'
            ]
        ], 403);
    }

    // Both liked each other -> matched
    return true;
}


    /**
     * Mark messages as read
     *
     * @param int $currentUserId
     * @param int $otherUserId
     * @return int
     */
    public function markAsRead($currentUserId, $otherUserId)
    {
        return DB::table('chats')
        ->where('sender_id', $otherUserId)
        ->where('receiver_id', $currentUserId)
        ->where('is_read', '0')
        ->update(['is_read' => '1']);
    }

    /**
     * Delete a message (soft delete)
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    public function deleteMessage($messageId, $userId)
    {
        $message = Chat::findOrFail($messageId);
        
        // Only the sender can delete the message
        if ($message->sender_id !== $userId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You can only delete your own messages.');
        }
        
        return $message->delete();
    }

    /**
     * Get unread message count for a user
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId)
    {
        return Chat::where('receiver_id', $userId)
            ->where('is_read', '0')
            ->count();
    }
    

    /**
     * Get chat details by ID with participant information
     *
     * @param int $chatId
     * @param int $userId
     * @return \stdClass|null
     */
    public function getChatWithDetails($chatId, $userId)
    {
        try {
            // First, verify the user is a participant in this chat
            $chat = \App\Models\Chat::where('id', $chatId)
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })
                ->first();

            if (!$chat) {
                return null;
            }

            // Get the other participant
            $otherUserId = $chat->sender_id == $userId ? $chat->receiver_id : $chat->sender_id;
            
            // Get the other user's details
            $otherUser = \App\Models\User::find($otherUserId);
            
            // Get the last message in this conversation if any exists
            $lastMessage = \App\Models\Chat::where(function($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $userId)
                          ->where('receiver_id', $otherUserId);
                })
                ->orWhere(function($query) use ($userId, $otherUserId) {
                    $query->where('sender_id', $otherUserId)
                          ->where('receiver_id', $userId);
                })
                ->latest()
                ->first();

            // Get unread message count
            $unreadCount = \App\Models\Chat::where('sender_id', $otherUserId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            // Format the response to match what the controller expects
            $result = new \stdClass();
            $result->id = $chat->id;
            $result->title = $otherUser ? $otherUser->name : 'Chat';
            $result->type = 'private';
            $result->created_at = $chat->created_at;
            $result->updated_at = $lastMessage ? $lastMessage->created_at : $chat->updated_at;
            $result->unread_count = $unreadCount;
            $result->lastMessage = $lastMessage;
            
            // Format participants
            $result->participants = collect([
                $otherUser,
                \App\Models\User::find($userId)
            ])->filter()
              ->map(function($user) use ($userId) {
                  return (object) [
                      'id' => $user->id,
                      'name' => $user->name,
                      'profile_photo_url' => $user->profile_photo_url ?? null,
                      'is_online' => $user->is_online ?? false,
                      'is_you' => $user->id === $userId
                  ];
              });

            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Error in getChatWithDetails: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
