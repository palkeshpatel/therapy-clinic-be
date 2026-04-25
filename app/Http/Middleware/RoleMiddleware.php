<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        $user->loadMissing('role');
        $userRole = optional($user->role)->role_name;

        if (! $userRole || ! in_array($userRole, $roles, true)) {
            return ApiResponse::error('Forbidden — insufficient role', 403);
        }

        return $next($request);
    }
}
