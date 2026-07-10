<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LeavePolicy extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'leave_policies';
    public $timestamps = false;
    protected $casts = [
        'allow_half_day' => 'boolean',
        'is_handover_required' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'description',
        'allow_half_day',
        'carry_forward_days',
        'carry_forward_expiry_month',
        'carry_forward_expiry_date',
        'is_handover_required',
        'handover_min_days',
        'min_notice_days',
        'requires_attachment',
        'is_paid',
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
            'leavePolicyTiers',
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
    public function leavePolicyTiers()
    {
        return $this->hasMany(LeavePolicyTier::class, 'leave_policy_id', 'id')->active()
            ->orderBy('service_year_from', 'asc')
            ->orderBy('service_year_to', 'asc');
    }
}
