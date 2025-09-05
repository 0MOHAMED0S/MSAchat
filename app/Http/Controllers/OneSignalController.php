<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OneSignalController extends Controller
{
    public function savePlayerId(Request $request)
    {
        $request->validate(['player_id' => 'required|string']);
        $user = Auth::user();
        $user->onesignal_player_id = $request->player_id;
        $user->save();

        return response()->json(['status' => 'success']);
    }
}
