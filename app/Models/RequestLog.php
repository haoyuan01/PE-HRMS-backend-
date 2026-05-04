<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'request_logs';
    public $timestamps = false;
    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'files' => 'array',
        'cookies' => 'array',
        'performance' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'method',
        'path',
        'request_payload',
        'response_payload',
        'ip',
        'url',
        'scheme',
        'host',
        'port',
        'server_name',
        'files',
        'cookies',
        'user_agent',
        'status_code',
        'success',
        'performance',
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
            'activityLogs',
            'errorLogs',
        ])->where('uuid', $uuid)
        ->firstOrFail();
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'request_log_uuid', 'uuid');
    }

    public function errorLogs()
    {
        return $this->hasMany(ErrorLog::class, 'request_log_uuid', 'uuid');
    }
}
