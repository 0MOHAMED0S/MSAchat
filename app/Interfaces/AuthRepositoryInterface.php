<?php
namespace App\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findOrCreateGoogleUser($googleUser): User;
    public function logout(): void;
}
