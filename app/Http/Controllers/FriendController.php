<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
        public function index()
    {
                $users = User::where('id', '!=', auth()->id())
                     ->get();
        return view('chat.users',compact('users'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $users = User::where('name', 'like', "%{$query}%")
                     ->where('id', '!=', auth()->id())
                     ->get();

        return response()->json($users);
    }
}
