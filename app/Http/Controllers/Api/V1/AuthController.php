<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email'    => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $credentials = $request->only(['email', 'password']);

            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');

            $userModel = \App\Models\User::where('email', $credentials['email'])->first();
            if (! $userModel) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            try {
                $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($userModel->encrypted_password);
                if ($decrypted !== $credentials['password']) {
                    return ApiResponse::error('Invalid credentials', 401);
                }
            } catch (\Exception $e) {
                return ApiResponse::error('Invalid credentials', 401);
            }

            $token = $guard->login($userModel);

            $user = $guard->user();
            $user->loadMissing('role.permissions');

            return ApiResponse::success([
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'user'       => $user->toArrayWithPermissions(),
            ], 'Login successful');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (JWTException $e) {
            return ApiResponse::error('Auth error', 500);
        }
    }

    // ── OTP Login ─────────────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => ['required', 'email'],
            ]);

            $email = strtolower(trim($request->input('email')));

            // Check user exists and is active — show clear error if not
            $user = User::where('email', $email)->where('status', 'active')->first();
            if (! $user) {
                return ApiResponse::error('No active account found with this email address.', 404);
            }

            // Generate 6-digit OTP
            $otp      = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'otp:' . $email;

            // Store OTP in cache for 10 minutes
            Cache::put($cacheKey, $otp, 600);

            // Send OTP email via MailHelper (synchronous — fast Gmail SMTP)
            $userName = $user->name;
            $html = MailHelper::template("
                <p style='color:#111827;font-size:15px;'>Hello <strong>{$userName}</strong>,</p>
                <p style='color:#374151;'>Use the one-time code below to sign in. It expires in <strong>10 minutes</strong>.</p>
                <div style='background:#f5f3ff;border:2px dashed #7c3aed;border-radius:10px;padding:20px;text-align:center;margin:20px 0;'>
                    <span style='font-size:36px;font-weight:800;letter-spacing:10px;color:#7c3aed;'>{$otp}</span>
                </div>
                <p style='color:#9ca3af;font-size:12px;'>If you did not request this, please ignore this email.</p>
            ");

            MailHelper::send($email, $userName, 'Your Helping Hands Login OTP', $html,
                "Your Helping Hands OTP is: {$otp}. It expires in 10 minutes.");

            return ApiResponse::success(null, 'OTP sent to ' . $email);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Throwable $e) {
            Log::error('OTP email failed', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to send OTP email. Please try again.', 500);
        }
    }


    public function verifyOtp(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => ['required', 'email'],
                'otp'   => ['required', 'string', 'size:6'],
            ]);

            $email    = strtolower(trim($request->input('email')));
            $otp      = trim($request->input('otp'));
            $cacheKey = 'otp:' . $email;

            $storedOtp = Cache::get($cacheKey);

            if (! $storedOtp || $storedOtp !== $otp) {
                return ApiResponse::error('Invalid or expired OTP', 401);
            }

            // OTP is valid — delete it so it can't be reused
            Cache::forget($cacheKey);

            $user = User::where('email', $email)->where('status', 'active')->first();
            if (! $user) {
                return ApiResponse::error('User not found', 401);
            }

            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');
            $token = $guard->login($user);

            $user->loadMissing('role.permissions');

            return ApiResponse::success([
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'user'       => $user->toArrayWithPermissions(),
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
        $user?->loadMissing('role.permissions');

        return ApiResponse::success(
            $user ? $user->toArrayWithPermissions() : null,
            'OK'
        );
    }

    public function refresh()
    {
        try {
            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');

            $token = $guard->refresh();
            $user = $guard->user();
            $user?->loadMissing('role.permissions');

            return ApiResponse::success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60,
                'user' => $user ? $user->toArrayWithPermissions() : null,
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

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if (! $user) {
                return ApiResponse::error('Unauthenticated', 401);
            }

            $this->validate($request, [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            $user->fill($request->only(['name', 'email', 'phone']));
            $user->save();
            $user->load('role');

            return ApiResponse::success($user, 'Profile updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
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

