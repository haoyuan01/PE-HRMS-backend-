<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\OfficeBranch;

class OfficeBranchService
{
    public function findByUUID($uuid): OfficeBranch
    {
        $data = OfficeBranch::firstWhere('uuid', $uuid);
        throw_if(!$data, AppException::class, 'Office branch not found');
        
        return $data;
    }
}
