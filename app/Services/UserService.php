<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\User;

class UserService
{
    public function findByEmail($email, bool $fail = true): ?User
    {
        $data = User::with([
            'roles.permissions',
        ])->firstWhere('email', $email);

        throw_if(!$data && $fail, AppException::class, 'User not found');
        
        return $data;
    }
}
