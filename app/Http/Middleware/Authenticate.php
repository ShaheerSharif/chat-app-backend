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
            return $this->jsonError('Authentication failed', 500);
        }
    }

    protected function authenticate($request, array $guards)
    {
        // Check for user cookie
        if ($request->hasCookie('user')) {
            $user = json_decode($request->cookie('user'), true);

            if ($user && $this->authService->checkUserExistsById($user->id)) {
                return;
            }
        }

        throw new \Illuminate\Auth\AuthenticationException('Authentication required', $guards);
    }

    protected function unauthenticated($request, array $guards)
    {
        return $this->jsonError('Authentication required', 401);
    }
}
