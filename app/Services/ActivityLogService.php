<?php

namespace App\Services;

use Spatie\Activitylog\LogOptions;

class ActivityLogService
{
    public function __construct()
    {
    }

    public static function defaultOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
