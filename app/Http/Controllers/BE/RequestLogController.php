<?php

namespace App\Http\Controllers\BE;

use App\Filters\RequestLogFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestLogIndexRequest;
use App\Http\Requests\RequestLogShowRequest;
use App\Http\Resources\RequestLogResource;
use App\Models\RequestLog;

class RequestLogController extends Controller
{
    public function __construct(private RequestLogFilter $request_log_filter)
    {
    }

    public function index(RequestLogIndexRequest $request)
    {
        $request_log = RequestLog::with([
            'user',
        ]);

        $request_log = $this->request_log_filter->apply($request, $request->size, $request_log);

        return self::responsePaginated(RequestLogResource::collection($request_log), $request_log);
    }

    public function show(RequestLogShowRequest $request, string $uuid)
    {
        $request_log = RequestLog::findByUuid($uuid);
        
        return self::response(new RequestLogResource($request_log));
    }
}
