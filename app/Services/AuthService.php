<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(string $name, string $email, string $password)
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function check_user_exists(string $email, string $password): ?int
    {
        $user = User::where('email', $email)->select('id', 'password')->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user->id;
        }
        return null;
    }

    public function get_user_by_id(int $userId): ?User
    {
        return User::find($userId);
    }
}
