<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\Department;

class DepartmentService
{
    public function findByUUID($uuid): Department
    {
        $data = Department::firstWhere('uuid', $uuid);
        throw_if(!$data, AppException::class, 'Department not found');
        
        return $data;
    }
}
