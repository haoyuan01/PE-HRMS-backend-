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
        'passcode',
        'token',
        'secret',
        'card_number',
        'cvv',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $uuid = (string) Str::uuid();

        $request->attributes->set('request_log_uuid', $uuid);

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
                'uuid'             => $request->attributes->get('request_log_uuid'),
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
}