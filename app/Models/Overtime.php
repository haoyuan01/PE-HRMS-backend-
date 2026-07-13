<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;

class Overtime extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'overtimes';
    public $timestamps = false;
    protected $casts = [
        'manager_action_at' => 'datetime:Y-m-d H:i:s.u',
        'manager_approved' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'manager_approver_id',
        'manager_action_by',
        'manager_action_at',
        'manager_approved',
        'manager_remark',
        'description',
        'total_days',
        'attachment_path',
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
            'managerActionBy.personal',
            'managerActionBy.contact',
            'managerActionBy.employment.office',
            'managerActionBy.employment.position',
            'managerActionBy.employment.department',
            'managerActionBy.emergency',
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

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approver_id', 'id')->active();
    }

    public function managerActionBy()
    {
        return $this->belongsTo(User::class, 'manager_action_by', 'id')->active();
    }
}
