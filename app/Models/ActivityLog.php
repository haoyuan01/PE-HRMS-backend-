<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    public $timestamps = false;
    protected $casts = [
        'properties' => 'array',
        'performance' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'request_log_uuid',
        'log_name',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'performance',
        'old_values',
        'new_values',
        'batch_uuid',
        'created_at',
        'updated_at',
    ];

    /**
     * Data Retrieval Methods
     */
    public static function findByUuid(string $uuid)
    {
        return self::with([
            'user',
            'requestLog',
        ])->where('uuid', $uuid)
        ->firstOrFail();
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'causer_id', 'id');
    }

    public function requestLog()
    {
        return $this->belongsTo(RequestLog::class, 'request_log_uuid', 'uuid');
    }
}
