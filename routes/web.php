<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FriendController;

Route::get('/login', function () {
    return view('chat.login');
})->name('login')->middleware('guest');

Route::get('auth/google', [AuthController::class, 'redirect'])->name('login.google');
Route::get('auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::middleware('auth')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/users', [FriendController::class, 'index'])->name('chat.users');
    Route::get('/search', [FriendController::class, 'search'])->name('chat.search');

    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');

    // ✅ Two send routes
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send.new'); // for first message
    Route::post('/chat/{conversation}/send', [ChatController::class, 'send'])->name('chat.send'); // for existing

    Route::post('/chat/typing/{receiverId}', [ChatController::class, 'typing'])->name('chat.typing');
    Route::post('/chat/{conversation}/read', [ChatController::class, 'markAsRead'])->name('chat.read');
});
