<?php

namespace App\Http\Controllers\BE;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthForgotPasswordEmailRequest;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Mail\ForgotPasswordMail;
use App\Mail\ForgotPasscodeMail;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth_service)
    {
    }

    public function login(AuthLoginRequest $request)
    {        
        $user = User::findByEmail($request->email);

        $this->auth_service->validateUser($request, $user);

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

    public function forgotPassword(AuthForgotPasswordEmailRequest $request)
    {
        $user = User::findByEmail($request->email, false);

        DB::beginTransaction();

        try {

            $token = Password::createToken($user);

            $data = [
                'reset_password_token' => $token,
            ];

            DB::commit();

            return self::response($data);

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function forgotPasswordEmail(AuthForgotPasswordEmailRequest $request)
    {
        $user = User::findByEmail($request->email, false);

        DB::beginTransaction();

        try {

            $token = Password::createToken($user);

            $reset_password_link = url('/reset-password?token=' . $token . '&email=' . $user->email);

            $data = [
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'subject' => 'PE Portal - Reset Password',
                'reset_password_link' => $reset_password_link,
            ];

            Mail::to($user->email)->send(new ForgotPasswordMail($data));

            DB::commit();
            
            return self::response(null, 'Password reset email sent');

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function resetPassword(AuthResetPasswordRequest $request)
    {
        $user = User::findByEmail($request->email);

        throw_if(!Password::tokenExists($user, $request->reset_password_token), AppException::class, 'Invalid token');

        DB::beginTransaction();

        try {

            $user->password = Hash::make($request->password);
            $user->save();

            Password::deleteToken($user);

            DB::commit();

            return self::response(null);

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function forgotPasscodeEmail(Request $request)
    {
        $user = self::auth();

        DB::beginTransaction();

        try {

            $token = Password::createToken($user);

            $reset_passcode_link = url('/reset-passcode?token=' . $token . '&email=' . $user->email);

            $data = [
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'subject' => 'PE Portal - Reset System PIN',
                'reset_passcode_link' => $reset_passcode_link,
            ];

            Mail::to($user->email)->send(new ForgotPasscodeMail($data));

            DB::commit();

            return self::response(null, 'Passcode email sent');

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }
}
