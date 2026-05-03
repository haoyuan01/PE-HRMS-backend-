<?php

namespace App\Services;

use App\Models\ErrorLog;
use App\Models\RequestLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ErrorLogService
{
    public static function capture(Throwable $exception): void
    {
        try {
            $request = request();

            ErrorLog::create([
                'uuid'               => (string) Str::uuid(),
                'user_id'            => self::resolveUserId($request),
                'level'              => self::resolveLevel($exception),
                'exception_class'    => get_class($exception),
                'message'            => $exception->getMessage(),
                'exception_code'     => $exception->getCode() ?: null,
                'auth_guard'         => self::resolveAuthGuard(),
                'source_file'        => $exception->getFile(),
                'source_line'        => $exception->getLine(),
                'stack_trace'        => $exception->getTraceAsString(),
                'previous_exception' => self::resolvePrevious($exception),
                'performance'        => self::resolvePerformance(),
                'hostname'           => gethostname() ?: null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (Throwable) {
            // Logging must never crash the application
        }
    }

    // -------------------------------------------------------------------------
    // Private resolvers
    // -------------------------------------------------------------------------

    private static function resolveLevel(Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ValidationException                              => 'warning',
            $exception instanceof AuthenticationException                          => 'warning',
            $exception instanceof AuthorizationException                           => 'warning',
            $exception instanceof ModelNotFoundException                           => 'warning',
            $exception instanceof HttpException && $exception->getStatusCode() < 500 => 'warning',
            $exception instanceof QueryException                                   => 'critical',
            default                                                                => 'error',
        };
    }

    private static function resolveUserId(?Request $request): ?int
    {
        try {
            return Auth::id() ?? $request?->user()?->id;
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolveAuthGuard(): ?string
    {
        try {
            foreach (array_keys(config('auth.guards', [])) as $guard) {
                if (Auth::guard($guard)->check()) {
                    return $guard;
                }
            }
        } catch (Throwable) {}

        return null;
    }

    private static function resolvePrevious(Throwable $exception): ?array
    {
        $previous = $exception->getPrevious();

        if (!$previous) {
            return null;
        }

        return [
            'class'   => get_class($previous),
            'message' => $previous->getMessage(),
            'code'    => $previous->getCode(),
            'file'    => $previous->getFile(),
            'line'    => $previous->getLine(),
        ];
    }

    private static function resolvePerformance(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'memory_peak_mb'  => round(memory_get_peak_usage(true) / 1048576, 2),
            'uptime_ms'       => defined('LARAVEL_START')
                ? (int) ((microtime(true) - LARAVEL_START) * 1000)
                : null,
        ];
    }
}