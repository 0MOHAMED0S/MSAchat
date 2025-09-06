<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FriendController extends Controller
{
public function index()
{
    try {
        $authId = auth()->id();

        if (!$authId) {
            return redirect()->route('login')->with('error', 'You must be logged in to see users.');
        }

        $users = User::where('id', '!=', $authId)
            ->orderBy('id', 'asc')
            ->get();

        return view('chat.users', compact('users'));
    } catch (Exception $e) {
        Log::error('Users List Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong while loading users.');
    }
}



    public function search(Request $request)
{
    try {
        $validated = $request->validate([
            'query' => 'nullable|string|max:150'
        ]);

        $authId = auth()->id();

        if (!$authId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 401);
        }

        $query = $validated['query'] ?? '';

        $users = User::where('name', 'like', "%{$query}%")
            ->where('id', '!=', $authId)
            ->limit(20) // prevent heavy query
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    } catch (Exception $e) {
        Log::error('User Search Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to search users.'
        ], 500);
    }
}

}
