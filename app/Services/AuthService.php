<?php

namespace App\Services;

use App\Constants\ConfigurationCodeConstants;
use App\Constants\HttpStatusCodeConstants;
use App\Exceptions\AppException;
use App\Models\Configuration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    public function validateUser(Request $request, User $user): void
    {
        $auth_login_max_attempts = (int) Configuration::findByKey(ConfigurationCodeConstants::AUTH_LOGIN_MAX_ATTEMPTS)->value;
        $auth_login_lockout_duration_minutes = (int) Configuration::findByKey(ConfigurationCodeConstants::AUTH_LOGIN_LOCKOUT_DURATION_MINUTES)->value;
        
        $key = Str::lower($request->email).'|'.$request->ip(); // key for rate limiting

        if (RateLimiter::tooManyAttempts($key, $auth_login_max_attempts)) // check if too many attempts
        {
            throw new AppException('Too many login attempts. Try again later.', HttpStatusCodeConstants::TOO_MANY_REQUESTS);
        }

        try {
            
            $this->validatePassword($user, $request->password); // check password credentials

        } catch (\Exception $e) {

            RateLimiter::hit($key, $auth_login_lockout_duration_minutes * 60); // increase failed attempts
            throw $e;

        }

        RateLimiter::clear($key); // clear rate limit if login is successful
    }

    public function validatePassword(User $user, string $password): bool
    {
        $check_password = Hash::check($password, $user->password);
        throw_if(!$check_password, AppException::class, 'Incorrect credentials');

        return $check_password;
    }

    public function validatePasscode(User $user, string $passcode): bool
    {
        throw_if(!$user->passcode, AppException::class, 'Passcode not set');

        $check_passcode = Hash::check($passcode, $user->passcode);
        throw_if(!$check_passcode, AppException::class, 'Incorrect passcode');

        return $check_passcode;
    }
}
