<?php

namespace App\Http\Controllers;

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
            ]);
        } catch (ValidationException $e) {
            return $this->jsonError('Validation failed', 422);
        }

        $userId = $this->authService->checkUserExists($request->email, $request->password);

        if (!$userId) {
            return $this->jsonError('Invalid credentials', 401);
        }

        return $this
            ->jsonSuccess('Login successful', ['user' => ['id' => $userId ]])
            ->cookie(
                'user_id',
                $userId,
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
        return $this
            ->jsonSuccess('Logout successful')
            ->cookie(
                'user_id',
                '',
                -1,
                null,
                null,
                config('session.secure'),
                true
            );
    }
}
