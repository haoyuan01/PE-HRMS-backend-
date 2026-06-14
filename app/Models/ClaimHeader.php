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
        'approved_at' => 'datetime:Y-m-d H:i:s.u',
        'paid_at' => 'datetime:Y-m-d H:i:s.u',
        'rejected_at' => 'datetime:Y-m-d H:i:s.u',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'approver_id',
        'approved_at',
        'payer_id',
        'paid_at',
        'rejected_by',
        'rejected_at',
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
            'claimItems',
            'user',
            'approver',
            'payer',
            'rejectedBy',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id', 'id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by', 'id');
    }

    public function items()
    {
        return $this->claimItems();
    }

    public function claimItems()
    {
        return $this->hasMany(ClaimItem::class, 'claim_header_id', 'id')->active();
    }
}
