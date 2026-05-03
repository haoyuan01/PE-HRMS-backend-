<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogApiRequest
{
    /**
     * Fields that should never be logged.
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'card_number',
        'cvv',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $start_time = hrtime(true);

        $response = $next($request);

        $duration_ms = (int) ((hrtime(true) - $start_time) / 1e6);

        // start request        
        $start_memory = memory_get_usage(true);

        // after request
        $end_memory = memory_get_usage(true);
        $peak_memory = memory_get_peak_usage(true);

        $memory_used_mb = round(($end_memory - $start_memory) / 1024 / 1024, 2); // actual request usage
        $memory_peak_mb = round($peak_memory / 1024 / 1024, 2); // overall peak

        $this->record($request, $response, $duration_ms, $memory_used_mb, $memory_peak_mb);

        return $response;
    }

    private function record(Request $request, Response $response, int $duration_ms, float $memory_used_mb, float $memory_peak_mb): void
    {
        try {
            RequestLog::create([
                'uuid'             => (string) Str::uuid(),
                'user_id'          => $request->user()?->id,
                'method'           => $request->method(),
                'path'             => $request->path(),
                'files'            => collect($request->files->all())->map(fn($file) => [
                    'name'      => $file->getClientOriginalName(),
                    'size'      => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'tmp_name'  => $file->getPathName(),
                ])->toArray(),
                'request_payload'  => $this->sanitize($request->except(self::SENSITIVE_FIELDS)),
                'response_payload' => $this->parseResponse($response),
                'ip'               => $request->ip(),
                'url'              => $request->url(),
                'scheme'           => $request->getScheme(),
                'host'             => $request->getHost(),
                'port'             => $request->getPort(),
                'cookies'          => $request->cookies->all(),
                'user_agent'       => $request->userAgent(),
                'status_code'      => $response->getStatusCode(),
                'success'          => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'performance' => [
                    'duration_ms'      => $duration_ms,
                    'memory_used_mb'   => $memory_used_mb,   // actual request usage
                    'memory_peak_mb'   => $memory_peak_mb,   // overall peak
                ],
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the application
        }
    }

    private function sanitize(array $data): ?array
    {
        return empty($data) ? null : $data;
    }

    private function parseResponse(Response $response): ?array
    {
        $content = $response->getContent();

        if (!$content) {
            return null;
        }

        $decoded = json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
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