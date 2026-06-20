<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthResetPasscodeActionFE;
use App\Http\Requests\AuthResetPasswordActionFE;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthControllerFE extends Controller
{
    public function __construct()
    {
    }

    public function resetPassword(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
    
        if (!$token || !$email)
        {
            return view('auth.reset-password-invalid');
        }
    
        $user = User::findByEmail($email, false);
    
        if (!$user || !Password::tokenExists($user, $token))
        {
            return view('auth.reset-password-invalid');
        }
    
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'name' => trim($user->first_name . ' ' . $user->last_name),
            'action_url' => url('/reset-password'),
        ]);
    }

    public function resetPasswordAction(AuthResetPasswordActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('auth.reset-password-invalid');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Password::deleteToken($user);

        Auth::logoutOtherDevices($request->password);

        return redirect('/reset-password-success');
    }

    public function resetPasswordSuccess()
    {
        return view('auth.reset-password-success');
    }

    public function resetPasscode(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email)
        {
            return view('auth.reset-passcode-invalid');
        }

        $user = User::findByEmail($email, false);

        if (!$user || !Password::tokenExists($user, $token))
        {
            return view('auth.reset-passcode-invalid');
        }

        return view('auth.reset-passcode', [
            'token' => $token,
            'email' => $email,
            'name' => trim($user->first_name . ' ' . $user->last_name),
            'action_url' => url('/reset-passcode'),
        ]);
    }

    public function resetPasscodeAction(AuthResetPasscodeActionFE $request)
    {
        $user = User::findByEmail($request->email, false);

        if (!$user || !Password::tokenExists($user, $request->token))
        {
            return view('auth.reset-passcode-invalid');
        }

        $user->passcode = Hash::make($request->passcode);
        $user->save();

        Password::deleteToken($user);

        return redirect('/reset-passcode-success');
    }

    public function resetPasscodeSuccess()
    {
        return view('auth.reset-passcode-success');
    }
}
