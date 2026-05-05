<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'departments';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'description',
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
        $query = self::where('uuid', $uuid)
            ->where('is_active', StatusCodeConstants::ACTIVE);

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
