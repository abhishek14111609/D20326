<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.{userId}', function (User $user, $userId) {
    // Ensure the authenticated user is either the sender or receiver
    return (int) $user->id === (int) $userId;
});

// Presence channel for typing indicators
Broadcast::channel('typing.{conversationId}', function (User $user, $conversationId) {
    // Verify user is part of the conversation
    $conversation = \App\Models\Conversation::findOrFail($conversationId);
    
    return [
        'id' => $user->id,
        'name' => $user->name,
        'is_typing' => false
    ];
});

// Private channel for user notifications
Broadcast::channel('user.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});

// RTM channels for real-time messaging
Broadcast::channel('rtm.user.{userId}', function (User $user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('rtm.conversation.{conversationId}', function (User $user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && ($conversation->user1_id === $user->id || $conversation->user2_id === $user->id);
});
