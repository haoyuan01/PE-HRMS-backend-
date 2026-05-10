<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserPersonal extends Model
{
    use HasActivityLog;

    protected $table = 'user_personals';
    public $timestamps = false;
    public $casts = [
        'passport_expiry_date' => 'date:Y-m-d',
        'gender' => 'boolean',
        'is_married' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public $fillable = [
        'uuid',
        'user_id',
        'full_name',
        'first_name',
        'last_name',
        'identity_number',
        'passport_number',
        'passport_expiry_date',
        'blood_type',
        'image_path',
        'gender',
        'is_married',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * scopes
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', StatusCodeConstants::ACTIVE);
    }

    /**
     * Data Retrieval Methods
     */
    public static function findByUuid(string $uuid, bool $fail = true)
    {
        $query = self::with([
            'user',
        ])->where('uuid', $uuid)
        ->active();

        if ($fail)
        {
            return $query->firstOrFail();
        }

        return $query->first();
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
