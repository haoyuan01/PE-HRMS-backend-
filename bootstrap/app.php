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
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
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
        | Exception - Database Query Exception
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (QueryException $e, Request $request) {

            ErrorLogService::capture($e); // Log the exception in error_logs

            if ($request->is('api/*'))
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Database Query Error',
                    'data'    => null,
                ], 500);
            }
        });
        
        /*
        |--------------------------------------------------------------------------
        | Exception - Validation Exception
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (ValidationException $e, Request $request) {

            ErrorLogService::capture($e); // Log the exception in error_logs
            
            return null;
        });

        /*
        |--------------------------------------------------------------------------
        | Exception - Authorization Exception
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            
            ErrorLogService::capture($e); // Log the exception in error_logs
            
            return null;
        });

        /*
        |--------------------------------------------------------------------------
        | Exception - Model Not Found
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (\Throwable $e, Request $request) {
            
            ErrorLogService::capture($e); // Log the exception in error_logs

            if (!$request->is('api/*'))
            {
                return null;
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching record found',
                    'data' => null,
                ], HttpStatusCodeConstants::NOT_FOUND);
            }

            return null;
        });

        /*
        |--------------------------------------------------------------------------
        | Exception - Authentication Failure
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (AuthenticationException $e, Request $request) {

            ErrorLogService::capture($e); // Log the exception in error_logs
            
            return response()->json([
                'status' => false,
                'message' => 'Access denied',
                'data' => null,
            ], HttpStatusCodeConstants::UNAUTHORIZED);
        });

        /*
        |--------------------------------------------------------------------------
        | Exception - Route Not Found
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {

            ErrorLogService::capture($e); // Log the exception in error_logs

            if ($request->is('api/*'))
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Route Not Found.',
                    'data'    => null,
                ], HttpStatusCodeConstants::NOT_FOUND);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Exception - Permission Denied
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (UnauthorizedException $e, Request $request) {

            ErrorLogService::capture($e); // Log the exception in error_logs
            
            if ($request->is('api/*'))
            {                
                return response()->json([
                    'success' => false,
                    'message' => 'Access Prohibited',
                    'data'    => null,
                ], HttpStatusCodeConstants::FORBIDDEN);
            }
        });

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

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : HttpStatusCodeConstants::INTERNAL_SERVER_ERROR;

            // ValidationException handling
            if ($e instanceof \Illuminate\Validation\ValidationException)
            {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors(), // always array
                    'data' => null,
                ], HttpStatusCodeConstants::UNPROCESSABLE_ENTITY);
            }

            // Default error message normalization
            $message = $e->getMessage();

            if ($message instanceof \Illuminate\Support\MessageBag)
            {
                $message = $message->toArray();
            }

            if (is_array($message))
            {
                $message = array_values($message);
            }
            
            return response()->json([
                'success' => false,
                'message' => $message ?: 'Internal Server Error',
                'data' => null,
            ], $status);
        });

    })->create();
