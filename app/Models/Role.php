<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\HasActivityLog;

class Role extends SpatieRole
{
    use HasFactory, HasActivityLog;

    protected $table = 'roles';
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
    public static function findByUuid(string $uuid, bool $fail = true)
    {
        $query = self::with([
            'permissions',
        ])->where('uuid', $uuid);

        if ($fail)
        {
            return $query->firstOrFail();
        }

        return $query->first();
    }

    /**
     * Relationships
     */
}
