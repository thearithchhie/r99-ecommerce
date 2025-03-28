<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;

class CustomRoleMiddleware extends RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $role
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        // Admin users bypass role checks
        if ($authGuard->user()->is_admin) {
            return $next($request);
        }

        // For non-admin users, use Spatie's original role check
        return parent::handle($request, $next, $role, $guard);
    }
} 