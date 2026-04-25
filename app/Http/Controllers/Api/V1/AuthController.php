<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $credentials = $request->only(['email', 'password']);

            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');

            if (! $token = $guard->attempt($credentials)) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            $user = $guard->user();
            $user->loadMissing('role');

            return ApiResponse::success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'user' => $user,
            ], 'Login successful');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (JWTException $e) {
            return ApiResponse::error('Auth error', 500);
        }
    }

    public function me()
    {
        $user = Auth::guard('api')->user();
        $user?->loadMissing('role');

        return ApiResponse::success($user, 'OK');
    }

    public function refresh()
    {
        try {
            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');

            $token = $guard->refresh();
            $user = $guard->user();
            $user?->loadMissing('role');

            return ApiResponse::success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'user' => $user,
            ], 'Token refreshed');
        } catch (JWTException $e) {
            return ApiResponse::error('Unable to refresh token', 401);
        }
    }

    public function logout()
    {
        try {
            Auth::guard('api')->logout();
            return ApiResponse::success(null, 'Successfully logged out');
        } catch (JWTException $e) {
            return ApiResponse::error('Unable to logout', 400);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $this->validate($request, [
                'old_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:6'],
            ]);

            $user = Auth::guard('api')->user();
            if (! $user) {
                return ApiResponse::error('Unauthenticated', 401);
            }

            if (! Hash::check((string) $request->input('old_password'), (string) $user->password)) {
                return ApiResponse::error('Old password is incorrect', 422, [
                    'old_password' => ['Old password is incorrect.'],
                ]);
            }

            $user->password = Hash::make((string) $request->input('new_password'));
            $user->save();

            return ApiResponse::success(null, 'Password changed');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

