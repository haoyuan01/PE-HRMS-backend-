<?php

namespace App\Http\Controllers\BE;

use App\Filters\ErrorLogFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ErrorLogIndexRequest;
use App\Http\Requests\ErrorLogShowRequest;
use App\Http\Resources\ErrorLogResource;
use App\Models\ErrorLog;

class ErrorLogController extends Controller
{
    public function __construct(private ErrorLogFilter $error_log_filter)
    {
    }

    public function index(ErrorLogIndexRequest $request)
    {
        $error_log = ErrorLog::with([
            'user',
            'requestLog',
        ]);

        $error_log = $this->error_log_filter->apply($request, $request->size, $error_log);

        return self::responsePaginated(ErrorLogResource::collection($error_log), $error_log);
    }

    public function show(ErrorLogShowRequest $request, string $uuid)
    {
        $error_log = ErrorLog::findByUuid($uuid);
        
        return self::response(new ErrorLogResource($error_log));
    }
}
