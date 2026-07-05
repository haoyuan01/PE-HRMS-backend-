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
        'manager_action_at' => 'datetime:Y-m-d H:i:s.u',
        'manager_approved' => 'boolean',
        'director_action_at' => 'datetime:Y-m-d H:i:s.u',
        'director_approved' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'claim_header_id',
        'manager_action_by',
        'manager_action_at',
        'manager_approved',
        'director_action_by',
        'director_action_at',
        'director_approved',
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

    public function managerActionBy()
    {
        return $this->belongsTo(User::class, 'manager_action_by', 'id')->active();
    }

    public function directorActionBy()
    {
        return $this->belongsTo(User::class, 'director_action_by', 'id')->active();
    }
}
