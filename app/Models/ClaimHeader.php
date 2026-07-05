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
        'manager_reviewed_at' => 'datetime:Y-m-d H:i:s.u',
        'director_reviewed_at' => 'datetime:Y-m-d H:i:s.u',
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
        'manager_reviewed_by',
        'manager_reviewed_at',
        'director_reviewed_by',
        'director_reviewed_at',
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

            'managerReviewedBy.personal',
            'managerReviewedBy.contact',
            'managerReviewedBy.employment.office',
            'managerReviewedBy.employment.position',
            'managerReviewedBy.employment.department',
            'managerReviewedBy.emergency',

            'directorReviewedBy.personal',
            'directorReviewedBy.contact',
            'directorReviewedBy.employment.office',
            'directorReviewedBy.employment.position',
            'directorReviewedBy.employment.department',
            'directorReviewedBy.emergency',

            'claimItems.managerActionBy.personal',
            'claimItems.managerActionBy.contact',
            'claimItems.managerActionBy.employment.office',
            'claimItems.managerActionBy.employment.position',
            'claimItems.managerActionBy.employment.department',
            'claimItems.managerActionBy.emergency',

            'claimItems.directorActionBy.personal',
            'claimItems.directorActionBy.contact',
            'claimItems.directorActionBy.employment.office',
            'claimItems.directorActionBy.employment.position',
            'claimItems.directorActionBy.employment.department',
            'claimItems.directorActionBy.emergency',
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

    public function managerReviewedBy()
    {
        return $this->belongsTo(User::class, 'manager_reviewed_by', 'id')->active();
    }

    public function directorReviewedBy()
    {
        return $this->belongsTo(User::class, 'director_reviewed_by', 'id')->active();
    }

    public function claimItems()
    {
        return $this->hasMany(ClaimItem::class, 'claim_header_id', 'id')->active();
    }
}
