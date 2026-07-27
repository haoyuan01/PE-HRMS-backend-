<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasActivityLog;

    protected $table = 'leave_requests';
    public $timestamps = false;
    protected $casts = [
        'handover_action_at' => 'datetime:Y-m-d H:i:s.u',
        'handover_approved' => 'boolean',
        'manager_action_at' => 'datetime:Y-m-d H:i:s.u',
        'manager_approved' => 'boolean',
        'director_action_at' => 'datetime:Y-m-d H:i:s.u',
        'director_approved' => 'boolean',
        'resume_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'manager_approver_id',
        'leave_entitlement_id',
        'manager_action_by',
        'manager_action_at',
        'manager_approved',
        'manager_remark',
        'director_action_by',
        'director_action_at',
        'director_approved',
        'director_remark',
        'handover_by',
        'handover_action_at',
        'handover_approved',
        'handover_remark',
        'resume_date',
        'total_days',
        'reason',
        'attachment_url',
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
            'leaveRequestDays',

            'user.personal',
            'user.contact',
            'user.employment.office',
            'user.employment.position',
            'user.employment.department',
            'user.emergency',

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'leaveEntitlement.leavePolicy.leavePolicyTiers',

            'managerActionBy.personal',
            'managerActionBy.contact',
            'managerActionBy.employment.office',
            'managerActionBy.employment.position',
            'managerActionBy.employment.department',
            'managerActionBy.emergency',

            'directorActionBy.personal',
            'directorActionBy.contact',
            'directorActionBy.employment.office',
            'directorActionBy.employment.position',
            'directorActionBy.employment.department',
            'directorActionBy.emergency',

            'handoverBy.personal',
            'handoverBy.contact',
            'handoverBy.employment.office',
            'handoverBy.employment.position',
            'handoverBy.employment.department',
            'handoverBy.emergency',
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approver_id', 'id')->active();
    }

    public function leaveEntitlement()
    {
        return $this->belongsTo(LeaveEntitlement::class, 'leave_entitlement_id', 'id')->active();
    }

    public function managerActionBy()
    {
        return $this->belongsTo(User::class, 'manager_action_by', 'id')->active();
    }

    public function directorActionBy()
    {
        return $this->belongsTo(User::class, 'director_action_by', 'id')->active();
    }

    public function handoverBy()
    {
        return $this->belongsTo(User::class, 'handover_by', 'id')->active();
    }

    public function leaveRequestDates()
    {
        return $this->hasMany(LeaveRequestDate::class, 'leave_request_id', 'id')->active();
    }
}
