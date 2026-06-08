<?php

namespace App\Http\Controllers;

use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\SendsJSONResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use SendsJSONResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // POST /api/auth/login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'remember_me' => 'boolean',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError('Validation failed', 422);
        }

        $token = $request->remember_me ? StringHelper::generateRandomString(100) : null;

        $userId = $this->authService->login($request->email, $request->password, $token);

        if (!$userId) {
            return $this->jsonError('Invalid credentials', 401);
        }

        $data = [
            'id' => $userId,
        ];

        if ($token) {
            $data['remember_token'] = $token;
        }

        return $this
            ->jsonSuccess('Login successful', [ 'id' => $userId ])
            ->cookie(
                'user',
                json_encode($data),
                config('session.lifetime'),
                null,
                null,
                config('session.secure'),
                true
            );
    }

    // POST /api/auth/register
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError('Validation failed', 422);
        }

        try {
            $user = $this->authService->register($request->name, $request->email, $request->password);
        } catch (\Exception $e) {
            return $this->jsonError('Registration failed', 500);
        }

        return $this->jsonSuccess('Registration successful', ['user' => $user ]);
    }

    // GET /api/auth/logout
    public function logout(Request $request)
    {
        $user = json_decode($request->cookie('user'), true);

        if ($user) {
            $this->authService->resetRememberToken($user->id);
        }

        try {
            return $this
                ->jsonSuccess('Logout successful', [ 'id' => $user->id ])
                ->cookie(
                    'user',
                    '',
                    -1,
                    null,
                    null,
                    config('session.secure'),
                    true
                );
        } catch (\Exception $e) {
            return $this->jsonError('Logout failed', 500);
        }
    }
}
