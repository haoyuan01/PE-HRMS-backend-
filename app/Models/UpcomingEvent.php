<?php

namespace App\Models;

use App\Constants\StatusCodeConstants;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UpcomingEvent extends Model
{
    use HasActivityLog;

    protected $table = 'upcoming_events';
    public $timestamps = false;
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'location',
        'start_date',
        'end_date',
        'is_published',
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
            'upcomingEventImages',
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
    public function upcomingEventImages()
    {
        return $this->hasMany(UpcomingEventImage::class, 'upcoming_event_id', 'id')->active();
    }
}
