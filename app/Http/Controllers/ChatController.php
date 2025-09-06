<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index()
    {
        try {
            $authId = Auth::id();

            $conversations = Conversation::where('user_one_id', $authId)
                ->orWhere('user_two_id', $authId)
                ->with(['messages' => function ($q) {
                    $q->latest();
                }])
                ->get();

            $users = $conversations->map(function ($conversation) use ($authId) {
                $partnerId = $conversation->user_one_id == $authId
                    ? $conversation->user_two_id
                    : $conversation->user_one_id;

                $user = User::find($partnerId);

                if (!$user) {
                    return null;
                }

                $lastMessage = $conversation->messages->first();
                $user->last_message = $lastMessage;

                $unreadCount = $conversation->messages()
                    ->where('sender_id', $partnerId)
                    ->where('is_read', 0)
                    ->count();

                $user->unread_count = $unreadCount;

                return $user;
            })->filter();

            $users = $users->sortByDesc(function ($user) {
                return optional($user->last_message)->created_at;
            })->values();

            return view('chat.index', compact('users'));
        } catch (Exception $e) {
            Log::error('Chat Index Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading your chats.');
        }
    }




    //  Show chat with specific user (do not create conversation here)
    public function show($userId)
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Chat Show Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading the conversation.');
        }
    }


    public function send(Request $request, $conversationId = null)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $authId = Auth::id();

            //  If no conversationId → create one
            if (!$conversationId) {
                $request->validate([
                    'receiver_id' => 'required|integer|exists:users,id',
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
                'conversation_id' => $conversationId,
            ]);
        } catch (Exception $e) {
            Log::error('Chat Show Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading the conversation.');
        }
    }


    public function typing(Request $request, $receiverId)
    {
        try {
            broadcast(new UserTyping(
                auth()->id(),
                auth()->user()->name,
                $receiverId,
                $request->isTyping
            ));

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('Typing Event Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send typing event.'
            ], 500);
        }
    }


    public function markAsRead($conversationId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);
            $userId = auth()->id();

            $conversation->messages()
                ->where('sender_id', '!=', $userId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            broadcast(new MessageRead($conversation, $userId))->toOthers();

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            Log::error('MarkAsRead Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark messages as read.'
            ], 500);
        }
    }
}
