<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Models\Position;

class PositionService
{
    public function findByUUID($uuid): Position
    {
        $data = Position::firstWhere('uuid', $uuid);
        throw_if(!$data, AppException::class, 'Position not found');
        
        return $data;
    }
}
