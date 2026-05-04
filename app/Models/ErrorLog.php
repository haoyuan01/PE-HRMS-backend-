<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';
    public $timestamps = false;
    protected $casts = [
        'context'            => 'array',
        'previous_exception' => 'array',
        'performance'        => 'array',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'request_log_uuid',
        'level',
        'exception_class',
        'message',
        'exception_code',
        'source_file',
        'source_line',
        'stack_trace',
        'previous_exception',
        'performance',
        'hostname',
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function requestLog()
    {
        return $this->belongsTo(RequestLog::class, 'request_log_uuid', 'uuid');
    }
}
