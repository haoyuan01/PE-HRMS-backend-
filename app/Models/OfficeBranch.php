<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActivityLog;

class OfficeBranch extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'office_branches';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'address_1',
        'address_2',
        'address_3',
        'city',
        'state',
        'postcode',
        'country',
        'phone_code',
        'phone_number',
        'phone_iso',
        'fax_code',
        'fax_number',
        'fax_iso',
        'email',
        'is_active',
        'created_by',
        'updated_by',
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
