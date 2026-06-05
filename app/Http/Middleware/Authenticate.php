<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use App\Traits\SendsJSONResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    use SendsJSONResponse;

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
            return $next($request);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return $this->unauthenticated($request, $guards);
        }
    }

    protected function authenticate($request, array $guards)
    {
        // Check for user_id cookie
        if ($request->hasCookie('user_id')) {
            $userId = $request->cookie('user_id');
            $authService = new AuthService();

            if ($userId && $authService->check_user_exists_by_id($userId)) {
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
