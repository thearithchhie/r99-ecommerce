<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Support\Facades\Log;

class CustomRoleOrPermissionMiddleware extends RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $roleOrPermission
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $roleOrPermission = null, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        // Admin users bypass role and permission checks
        if ($authGuard->user()->is_admin) {
            return $next($request);
        }

        // For non-admin users, use Spatie's original role-or-permission check
        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode(',', $roleOrPermission);
            Log::info('Role or Permission check', [
                'roleOrPermission' => $roleOrPermission,
                'parsed' => $rolesOrPermissions
            ]);
        return parent::handle($request, $next, $roleOrPermission, $guard);
    }
} 