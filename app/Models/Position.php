<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasActivityLog;

class Position extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'positions';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'guard_name',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * Data Retrieval Methods
     */
    public static function findByUuid(string $uuid)
    {
        return self::where('uuid', $uuid)
            ->where('is_active', StatusCodeConstants::ACTIVE)
            ->firstOrFail();
    }

    /**
     * Relationships
     */
}
