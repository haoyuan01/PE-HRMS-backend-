<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClaimItem extends Model
{
    use HasActivityLog;

    protected $table = 'claim_items';
    public $timestamps = false;
    protected $casts = [
        'approved_at' => 'datetime:Y-m-d H:i:s.u',
        'released_at' => 'datetime:Y-m-d H:i:s.u',
        'rejected_at' => 'datetime:Y-m-d H:i:s.u',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'claim_header_id',
        'approved_by',
        'approved_at',
        'released_by',
        'released_at',
        'rejected_by',
        'rejected_at',
        'name',
        'amount',
        'date',
        'attachment_path',
        'remark',
        'is_active',
        'created_by',
        'updated_by',
        'created_at',
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
        $query = self::where('uuid', $uuid)
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
    public function claimHeader()
    {
        return $this->belongsTo(ClaimHeader::class, 'claim_header_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by', 'id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by', 'id');
    }
}
