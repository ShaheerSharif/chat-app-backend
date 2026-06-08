<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use App\Traits\SendsJSONResponse;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate extends Middleware
{
    use SendsJSONResponse;

    protected AuthService $authService;

    public function __construct(Auth $auth, AuthService $authService)
    {
        $this->authService = $authService;
        parent::__construct($auth);
    }

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
            return $next($request);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return $this->unauthenticated($request, $guards);
        } catch (\Exception $e) {
            return $this->jsonError('Internal Server Error', 500);
        }
    }

    protected function authenticate($request, array $guards)
    {
        // Check for user_id cookie
        if ($request->hasCookie('user_id')) {
            $userId = $request->cookie('user_id');

            if ($userId && $this->authService->checkUserExistsById($userId)) {
                return;
            }
        }

        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated', $guards);
    }

    protected function unauthenticated($request, array $guards)
    {
        return $this->jsonError('Unauthenticated', 401);
    }
}
