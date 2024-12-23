<?php

namespace App\Interfaces;

use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    public function register(array $data);
    public function verifyUser($email, $token): User | CustomException;
    public function login(array $data);
    public function logout();
    public function forgotPassword(User $user);
    public function resetPassword(User $user, string $token);
    public function updateProfile(Request $request): void;
}
