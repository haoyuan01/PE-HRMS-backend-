<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\User;

class UserService
{
    public function findByEmail($email): User
    {
        $data = User::with([
            'roles.permissions',
        ])->firstWhere('email', $email);
        throw_if(!$data, AppException::class, 'User not found');
        
        return $data;
    }
}
