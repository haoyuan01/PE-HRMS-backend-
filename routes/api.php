<?php

use App\Constants\HttpStatusCodeConstants;
use App\Http\Controllers\BE\AuthController;
use App\Http\Controllers\BE\LookupController;
use App\Http\Controllers\BE\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

$version = 'v1';

Route::group([
    'prefix' => $version,
    'middleware' => ['throttle:60,1'],
], function () {

    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function ($router) {

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('lookup')->group(function () {
            Route::get('permissions', [LookupController::class, 'permissions']);
            Route::get('users', [LookupController::class, 'users']);
        });

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{uuid}', [RoleController::class, 'show']);
            Route::put('/{uuid}', [RoleController::class, 'update']);
            Route::patch('/{uuid}', [RoleController::class, 'updateStatus']);
        });

        










        // middleware example usage
        Route::group(['middleware' => ['permission:Read User', 'role:Admin']], function() {

            Route::get('test-permission', function () {
                return 'test-permission middleware';
            });

        });
        
    });

});
