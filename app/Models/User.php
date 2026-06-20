<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Constants\StatusCodeConstants;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasActivityLog;

    protected $table = 'users';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'email',
        'password',
        'passcode',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'passcode',
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
    public static function findByEmail(string $email, bool $fail = true)
    {
        $query =  self::with([
            'roles.permissions',
        ])->where('email', $email)
        ->active();

        if ($fail)
        {
            return $query->firstOrFail();
        }

        return $query->first();
    }

    public static function findByUuid(string $uuid, bool $fail = true, bool $active = true)
    {
        $query = self::with([
            'personal',
            'contact',
            'employment.office',
            'employment.department',
            'employment.position',
            'emergency',
            'roles.permissions',
            'roles' => function ($query) {
                $query->where('is_active', StatusCodeConstants::ACTIVE);
            },
        ])->where('uuid', $uuid);

        if ($active)
        {
            $query->active();
        }

        if ($fail)
        {
            return $query->firstOrFail();
        }

        return $query->first();
    }

    /**
     * Relationships
     */
    public function personal()
    {
        return $this->hasOne(UserPersonal::class, 'user_id', 'id')->active();
    }
    
    public function contact()
    {
        return $this->hasOne(UserContact::class, 'user_id', 'id')->active();
    }
    
    public function employment()
    {
        return $this->hasOne(UserEmployment::class, 'user_id', 'id')->active();
    }
    
    public function emergency()
    {
        return $this->hasOne(UserEmergency::class, 'user_id', 'id')->active();
    }
    
}
