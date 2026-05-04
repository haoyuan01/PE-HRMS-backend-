<?php

namespace App\Http\Controllers\BE;

use App\Filters\ActivityLogFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLogIndexRequest;
use App\Http\Requests\ActivityLogShowRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogFilter $activity_log_filter)
    {
    }

    public function index(ActivityLogIndexRequest $request)
    {
        $activity_log = ActivityLog::with([
            'user',
            'requestLog',
        ]);

        $activity_log = $this->activity_log_filter->apply($request, $request->size, $activity_log);

        return self::responsePaginated(ActivityLogResource::collection($activity_log), $activity_log);
    }

    public function show(ActivityLogShowRequest $request, string $uuid)
    {
        $activity_log = ActivityLog::findByUuid($uuid);

        return self::response(ActivityLogResource::make($activity_log));
    }
}
