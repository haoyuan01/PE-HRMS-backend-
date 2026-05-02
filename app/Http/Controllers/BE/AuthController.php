<?php

namespace App\Http\Controllers\BE;

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

class AuthController extends Controller
{
    public function __construct(private AuthService $auth_service)
    {
    }

    public function login(AuthLoginRequest $request)
    {
        $user = User::findByEmail($request->email);

        $this->auth_service->validatePassword($user, $request->password);

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
