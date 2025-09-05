<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

// Private user channel for notifications or general events
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private conversation channel
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return Conversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user_one_id', $user->id)
              ->orWhere('user_two_id', $user->id);
        })->exists();
});

// Typing indicator channel (private to receiver)
Broadcast::channel('typing.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

// Chat list updates channel (last message & unread counters)
Broadcast::channel('chat-list.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Online users presence channel
Broadcast::channel('online-users', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
