<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Builder;

class Permission extends SpatiePermission
{
    use HasFactory, HasActivityLog;
    
    protected $table = 'permissions';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'guard_name',
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

    /**
     * Relationships
     */
}
