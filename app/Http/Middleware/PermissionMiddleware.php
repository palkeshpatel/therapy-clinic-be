<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, string $module, string $action = 'view')
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        if (! $user->hasPermission($module, $action)) {
            return ApiResponse::error('Forbidden — insufficient permission', 403);
        }

        return $next($request);
    }
}
