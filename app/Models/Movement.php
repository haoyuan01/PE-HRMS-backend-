<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Movement extends Model
{
    use HasFactory, HasActivityLog;

    protected $table = 'movements';
    public $timestamps = false;
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'movement_type_id',
        'location',
        'start_date',
        'end_date',
        'description',
        'attachment_path',
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
    public function movement_type()
    {
        return $this->belongsTo(MovementType::class, 'movement_type_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
