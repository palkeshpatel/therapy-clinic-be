<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdministratorMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        if (! $user->isAdministrator()) {
            return ApiResponse::error('Forbidden — administrator only', 403);
        }

        return $next($request);
    }
}
