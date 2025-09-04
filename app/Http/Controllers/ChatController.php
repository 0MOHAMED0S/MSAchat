<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // ✅ Show only users with existing conversations
public function index()
{
    $authId = Auth::id();

    $conversations = Conversation::where('user_one_id', $authId)
        ->orWhere('user_two_id', $authId)
        ->get();

    $users = $conversations->map(function ($conversation) use ($authId) {
        $partnerId = $conversation->user_one_id == $authId
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        $user = User::find($partnerId);

        // Last message
        $lastMessage = $conversation->messages()->latest()->first();
        $user->last_message = $lastMessage;

        // Unread messages count (from partner → me)
        $unreadCount = $conversation->messages()
    ->where('sender_id', $partnerId)
    ->where('is_read', 0)
    ->count();


        $user->unread_count = $unreadCount;

        return $user;
    });

    return view('chat.index', compact('users'));
}


    // ✅ Show chat with specific user (do not create conversation here)
    public function show($userId)
    {
        $authId = Auth::id();

        $conversation = Conversation::where(function ($q) use ($authId, $userId) {
            $q->where('user_one_id', $authId)->where('user_two_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('user_one_id', $userId)->where('user_two_id', $authId);
        })->first();

        $messages = $conversation
            ? $conversation->messages()->with('sender')->get()
            : collect();

        $receiver = User::findOrFail($userId);

        return view('chat.show', compact('conversation', 'messages', 'receiver'));
    }

public function send(Request $request, $conversationId = null)
{
    $request->validate([
        'message' => 'required|string',
    ]);

    $authId = Auth::id();

    // ✅ If no conversationId → create one
    if (!$conversationId) {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $conversation = Conversation::firstOrCreate([
            'user_one_id' => min($authId, $request->receiver_id),
            'user_two_id' => max($authId, $request->receiver_id),
        ]);

        $conversationId = $conversation->id;
    } else {
        $conversation = Conversation::findOrFail($conversationId);
    }

    $message = Message::create([
        'conversation_id' => $conversationId,
        'sender_id' => $authId,
        'message' => $request->message,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return response()->json([
        'message' => $message,
        'conversation_id' => $conversationId, // ✅ return id for frontend
    ]);
}


    public function typing(Request $request, $receiverId)
    {
        broadcast(new UserTyping(
            auth()->id(),
            auth()->user()->name,
            $receiverId,
            $request->isTyping
        ));

        return response()->json(['status' => 'ok']);
    }

public function markAsRead($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);
    $userId = auth()->id();

    // Update messages not sent by me
    $conversation->messages()
        ->where('sender_id', '!=', $userId)
        ->where('is_read', 0)
        ->update(['is_read' => 1]);

    // Broadcast read event
    broadcast(new MessageRead($conversation, $userId))->toOthers();

    return response()->json(['status' => 'success']);
}




}
