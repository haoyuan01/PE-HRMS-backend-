<?php

namespace App\Http\Controllers\BE;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\HttpStatusCodeConstants;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthForgotPasswordRequest;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth_service)
    {
    }

    public function login(AuthLoginRequest $request)
    {
        $key = Str::lower($request->email).'|'.$request->ip(); // key for rate limiting

        // throw error if too many attempts
        throw_if(RateLimiter::tooManyAttempts($key, ConfigurationCodeConstants::AUTH_LOGIN_MAX_ATTEMPTS), AppException::class, 'Too many login attempts. Try again later.', HttpStatusCodeConstants::TOO_MANY_REQUESTS);

        $user = User::findByEmail($request->email);

        try {

            $this->auth_service->validatePassword($user, $request->password); // validate email and password

        } catch (\Exception $e) {

            RateLimiter::hit($key, ConfigurationCodeConstants::AUTH_LOGIN_LOCKOUT_DURATION_MINUTES * 60); // increase failed attempts

            throw $e;
        }
        
        RateLimiter::clear($key); // clear failed attempts

        $token = $user->createToken('api-access')->plainTextToken;

        $data = [
            'user' => new UserResource($user),
            'token' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ];

        return self::response($data, 'Login success');
    }

    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return self::response(null, 'Logout success');
    }

    public function forgotPassword(AuthForgotPasswordRequest $request)
    {
        $user = User::findByEmail($request->email, false);
        
        $token = Password::createToken($user);

        $reset_password_link = url('/reset-password?token=' . $token . '&email=' . $user->email);

        $data = [
            'name' => trim($user->first_name . ' ' . $user->last_name),
            'subject' => 'PE Portal - Reset Password',
            'reset_password_link' => $reset_password_link,
        ];

        Mail::to($user->email)->send(new ForgotPasswordMail($data));
        
        return self::response(null, 'Password reset email sent');
    }

    public function resetPassword(AuthResetPasswordRequest $request)
    {
        $user = User::findByEmail($request->email);

        throw_if(!Password::tokenExists($user, $request->token), AppException::class, 'Invalid token');
        
        $user->password = Hash::make($request->password);
        $user->save();

        Password::deleteToken($user);

        return self::response(null);
    }
}
