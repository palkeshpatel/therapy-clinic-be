<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roleTypes)
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        $user->loadMissing('role');
        $userRoleType = optional($user->role)->role_type;

        if (! $userRoleType || ! in_array($userRoleType, $roleTypes, true)) {
            return ApiResponse::error('Forbidden — insufficient role type', 403);
        }

        return $next($request);
    }
}
