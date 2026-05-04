<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait HasActivityLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return ActivityLogService::defaultOptions();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties?->toArray() ?? [];
        $model = class_basename($activity->subject_type);

        $duration_ms = round((microtime(true) - LARAVEL_START) * 1000, 2); // total request execution time from framework bootstrap
        $memory_current_mb = round(memory_get_usage(true) / 1024 / 1024, 2); // current PHP memory allocation at execution time snapshot
        $memory_peak_mb = round(memory_get_peak_usage(true) / 1024 / 1024, 2); // peak memory usage during request lifecycle

        $request_log_uuid = request()->attributes->get('request_log_uuid');

        $activity->uuid = (string) Str::uuid();
        $activity->request_log_uuid = $request_log_uuid;
        $activity->event = $eventName;
        $activity->old_values = isset($properties['old']) ? json_encode($properties['old']) : null;
        $activity->new_values = isset($properties['attributes']) ? json_encode($properties['attributes']) : null;
        $activity->description = "{$model} {$eventName}";
        $activity->performance = json_encode([
            'duration_ms' => $duration_ms, // request time
            'memory_current_mb' => $memory_current_mb, // memory now
            'memory_peak_mb' => $memory_peak_mb, // peak memory
        ]);

        // following fields are logged automatically by spatie/activitylog
        // subject_type
        // subject_id
        // causer_type
        // causer_id
        // properties
    }

    // not using these for now
    private function getCapturableData(): array
    {
        return [                             
            'uuid' => (string) Str::uuid(),
            'ip' => request()->ip(),
            'device' => request()->header('User-Agent'),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'path' => request()->path(),
            'method' => request()->method(),
            'scheme' => request()->getScheme(),
            'headers' => collect(request()->headers->all())
                ->except(['authorization', 'cookie', 'x-api-key'])
                ->toArray(),
            'host' => request()->getHost(),
            'port' => request()->getPort(),
            'server_software' => request()->server('SERVER_SOFTWARE'),
            'server_name' => request()->server('SERVER_NAME'),
            'server_addr' => request()->server('SERVER_ADDR'),
            'server_port' => request()->server('SERVER_PORT'),
            'server_protocol' => request()->server('SERVER_PROTOCOL'),
            'body' => collect(request()->all())
                ->except(['password', 'password_confirmation', 'token'])
                ->toArray(),
            'request' => [
                'referer'          => request()->header('Referer'),
                'origin'           => request()->header('Origin'),
                'is_ajax'          => request()->ajax(),
                'is_json'          => request()->isJson(),
                'is_secure'        => request()->isSecure(),
                'accepts_json'     => request()->acceptsJson(),
                'content_type'     => request()->header('Content-Type'),
                'content_length'   => request()->header('Content-Length'),
                'accepts_language' => request()->header('Accept-Language'),
                'is_xhr'           => request()->header('X-Requested-With') === 'XMLHttpRequest',
            ],
            'query' => request()->query(), // check why empty
            'files' => collect(request()->files->all())->map(fn($file) => [
                'name'      => $file->getClientOriginalName(),
                'size'      => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'tmp_name'  => $file->getPathName(),
            ])->toArray(),
            'cookie' => request()->cookies->all(),
            'timestamp' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
            'request_id' => request()->header('X-Request-Id'),
            'trace_id' => request()->header('X-Trace-Id'),
            'correlation_id' => request()->header('X-Correlation-Id'),
            'user' => [
                'id'         => Auth::id(),
                'email'      => Auth::user()?->email,
            ],
            'auth' => [
                'guard'            => Auth::getDefaultDriver(),
                'is_impersonating' => session()->has('impersonated_by'),
                'impersonated_by'  => session()->get('impersonated_by'),
                'session_id'       => session()->getId(),
                'auth_via'         => Auth::user() ? 'authenticated' : 'guest',
            ],
            'route' => [
                'name'        => request()->route()?->getName(),
                'action'      => request()->route()?->getActionName(),
                'controller'  => request()->route()?->getControllerClass(),
                'middleware'  => request()->route()?->gatherMiddleware(),
                'parameters'  => request()->route()?->parameters(),
                'prefix'      => request()->route()?->getPrefix(),
            ],
            'performance' => [
                'duration_ms'  => round((microtime(true) - LARAVEL_START) * 1000, 2),
                'memory_usage' => memory_get_usage(true),
                'memory_peak'  => memory_get_peak_usage(true),
                'db_queries'   => app('db')->getQueryLog(), // check why empty
                'db_query_count' => count(app('db')->getQueryLog()),
                'db_query_time_ms' => round(array_sum(array_map(fn($q) => $q['time'], app('db')->getQueryLog())) * 1000, 2),
            ],
        ];
    }
}
