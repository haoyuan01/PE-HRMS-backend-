<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeaveEntitlement extends Model
{
    use HasActivityLog;

    protected $table = 'leave_entitlements';
    public $timestamps = false;
    protected $casts = [
        'carry_forward_expiry_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'leave_policy_id',
        'year',
        'entitled_days',
        'used_days',
        'balance_days',
        'carried_forward_days',
        'carry_forward_expiry_date',
        'is_active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * Scopes
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
            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',
            'user.certificates',
            'leavePolicy.leavePolicyTiers',
        ])->where('uuid', $uuid)
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->active();
    }

    public function leavePolicy()
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id', 'id')->active();
    }
}
