<?php

use App\Constants\HttpStatusCodeConstants;
use App\Http\Middleware\Authenticate;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Handle authentication failure
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied',
                'data' => null,
            ], HttpStatusCodeConstants::UNAUTHORIZED);
        });

        // Handle route not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*'))
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Route Not Found.',
                    'data'    => null,
                ], HttpStatusCodeConstants::NOT_FOUND);
            }
        });

        // Handle permission denied
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Prohibited',
                    'data'    => null,
                ], HttpStatusCodeConstants::FORBIDDEN);
            }
        });

        // Handle all other exceptions
        // $exceptions->render(function (\Throwable $e, Request $request) {
        //     if ($request->is('api/*'))
        //     {
        //         $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : HttpStatusCodeConstants::INTERNAL_SERVER_ERROR;
        
        //         return response()->json([
        //             'success' => false,
        //             'message' => $e->getMessage() ?: 'Internal Server Error',
        //             'data'    => null,
        //         ], $status);
        //     }
        // });
        $exceptions->render(function (\Throwable $e, Request $request) {

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
