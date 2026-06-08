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

    /**
     * Check if user exists with email and password. Stores remember token if provided.
     */
    public function login(string $email, string $password, ?string $rememberToken): ?int
    {
        $user = User::where('email', $email)->select(
            'id',
            'password',
            'remember_token',
        )->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if ($rememberToken) {
            $user->remember_token = $rememberToken;
            $user->save();
        }

        return $user->id;
    }

    public function resetRememberToken(int $userId)
    {
        return User::where('id', $userId)->update(['remember_token' => null]);
    }

    public function checkUserExistsById(int $userId): bool
    {
        return User::where('id', $userId)->exists();
    }

    public function getUserById(int $userId, array $columns = ['*']): ?User
    {
        $ignoreCols = ['password', 'remember_token'];
        $columns = array_values(array_diff($columns, $ignoreCols));

        return User::select($columns)->find($userId);
    }
}
