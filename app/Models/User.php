<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasActivityLog;

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
        'name',
        'full_name',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $hidden = [
        'password',
    ];
    
    /**
     * Data Retrieval Methods
     */
    public static function findByEmail(string $email, bool $fail = true)
    {
        $query =  self::with([
            'roles.permissions',
        ])->where('email', $email);

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
