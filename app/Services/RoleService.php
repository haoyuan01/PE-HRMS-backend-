<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleService
{
    public function findByUUID($uuid): Role
    {
        $data = Role::with([
            'permissions'
        ])->firstWhere('uuid', $uuid);
        throw_if(!$data, AppException::class, 'Role not found');
        
        return $data;
    }
}
