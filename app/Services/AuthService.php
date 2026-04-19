<?php

namespace App\Services;

use App\Exceptions\AppException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function validatePassword($user, $password): bool
    {
        $check_password = Hash::check($password, $user->password);
        throw_if(!$check_password, AppException::class, 'Incorrect credentials');

        return $check_password;
    }
}
