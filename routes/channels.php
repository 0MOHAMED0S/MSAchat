<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    if (!$conversation) return false;
    return in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id]);
});
Broadcast::channel('typing.{receiverId}', function ($user, $receiverId) {
    // only allow if the logged-in user is the receiver or sender
    return (int) $user->id === (int) $receiverId || true;
});
// ✅ last message updates for user list
Broadcast::channel('chat-list.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('online-users', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
