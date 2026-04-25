<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Authenticate
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, $guard = 'api')
    {
        try {
            if (! $user = $this->auth->guard($guard)->user()) {
                return ApiResponse::error('Unauthenticated', 401);
            }
        } catch (TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401);
        } catch (TokenInvalidException $e) {
            return ApiResponse::error('Token invalid', 401);
        } catch (JWTException $e) {
            return ApiResponse::error('Token absent or malformed', 401);
        }

        return $next($request);
    }
}
