<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'uuid',
        'level',
        'exception_class',
        'message',
        'exception_code',
        'user_id',
        'source_file',
        'source_line',
        'stack_trace',
        'previous_exception',
        'performance',
        'hostname',
    ];

    protected $casts = [
        'context'            => 'array',
        'previous_exception' => 'array',
        'performance'        => 'array',
    ];
}