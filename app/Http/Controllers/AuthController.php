<?php

namespace App\Http\Controllers;

use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account']) // force account selection
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();
        $user = $this->authRepository->findOrCreateGoogleUser($googleUser);

        return redirect()->route('chat.index');
    }
    public function logout()
    {
        $this->authRepository->logout();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
