<?php

use App\Constants\HttpStatusCodeConstants;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\LogApiRequest;
use App\Services\ErrorLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'auth' => Authenticate::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'log.request' => LogApiRequest::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Exception - All Other Exceptions
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (\Throwable $e, Request $request) {
            
            ErrorLogService::capture($e); // Log the exception in error_logs
            
            if (!$request->is('api/*'))
            {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : HttpStatusCodeConstants::INTERNAL_SERVER_ERROR;

            // ValidationException handling
            if ($e instanceof ValidationException)
            {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors(), // always array
                    'data' => null,
                ], HttpStatusCodeConstants::UNPROCESSABLE_ENTITY);
            }

            // QueryException handling
            if ($e instanceof QueryException)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Database Query Error',
                    'data' => null,
                ], HttpStatusCodeConstants::INTERNAL_SERVER_ERROR);
            }

            // ModelNotFoundException and NotFoundHttpException handling
            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching record found',
                    'data' => null,
                ], HttpStatusCodeConstants::NOT_FOUND);
            }

            // AuthenticationException handling
            if ($e instanceof AuthenticationException)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'data' => null,
                ], HttpStatusCodeConstants::UNAUTHORIZED);
            }

            // AuthorizationException and UnauthorizedException handling
            if ($e instanceof AuthorizationException || $e instanceof UnauthorizedException)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Access prohibited',
                    'data' => null,
                ], HttpStatusCodeConstants::FORBIDDEN);
            }

            // ValidationException handling
            if ($e instanceof ValidationException)
            {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors(),
                    'data' => null,
                ], HttpStatusCodeConstants::UNPROCESSABLE_ENTITY);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'data' => null,
            ], $status);
        });

    })->create();
