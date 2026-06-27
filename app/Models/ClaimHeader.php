<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClaimHeader extends Model
{
    use HasActivityLog;

    protected $table = 'claim_headers';
    public $timestamps = false;
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'manager_approver_id',
        'name',
        'remark',
        'total_amount',
        'start_date',
        'end_date',
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

            'managerApprover.personal',
            'managerApprover.contact',
            'managerApprover.employment.office',
            'managerApprover.employment.position',
            'managerApprover.employment.department',
            'managerApprover.emergency',

            'claimItems.approvedBy.personal',
            'claimItems.approvedBy.contact',
            'claimItems.approvedBy.employment.office',
            'claimItems.approvedBy.employment.position',
            'claimItems.approvedBy.employment.department',
            'claimItems.approvedBy.emergency',

            'claimItems.rejectedBy.personal',
            'claimItems.rejectedBy.contact',
            'claimItems.rejectedBy.employment.office',
            'claimItems.rejectedBy.employment.position',
            'claimItems.rejectedBy.employment.department',
            'claimItems.rejectedBy.emergency',
            
            'claimItems.releasedBy.personal',
            'claimItems.releasedBy.contact',
            'claimItems.releasedBy.employment.office',
            'claimItems.releasedBy.employment.position',
            'claimItems.releasedBy.employment.department',
            'claimItems.releasedBy.emergency',
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

    public function claimItems()
    {
        return $this->hasMany(ClaimItem::class, 'claim_header_id', 'id')->active();
    }
}
