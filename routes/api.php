<?php

use App\Constants\HttpStatusCodeConstants;
use App\Http\Controllers\BE\AuthController;
use App\Http\Controllers\BE\DepartmentController;
use App\Http\Controllers\BE\LookupController;
use App\Http\Controllers\BE\OfficeBranchController;
use App\Http\Controllers\BE\RoleController;
use App\Http\Controllers\PositionController;
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
            Route::get('departments', [LookupController::class, 'departments']);
            Route::get('positions', [LookupController::class, 'positions']);
            Route::get('office-branches', [LookupController::class, 'officeBranches']);
        });

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{uuid}', [RoleController::class, 'show']);
            Route::put('/{uuid}', [RoleController::class, 'update']);
            Route::patch('/{uuid}', [RoleController::class, 'updateStatus']);
        });

        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::get('/{uuid}', [DepartmentController::class, 'show']);
            Route::put('/{uuid}', [DepartmentController::class, 'update']);
            Route::patch('/{uuid}', [DepartmentController::class, 'updateStatus']);
        });
        
        Route::prefix('positions')->group(function () {
            Route::get('/', [PositionController::class, 'index']);
            Route::post('/', [PositionController::class, 'store']);
            Route::get('/{uuid}', [PositionController::class, 'show']);
            Route::put('/{uuid}', [PositionController::class, 'update']);
            Route::patch('/{uuid}', [PositionController::class, 'updateStatus']);
        });
        
        Route::prefix('office-branches')->group(function () {
            Route::get('/', [OfficeBranchController::class, 'index']);
            Route::post('/', [OfficeBranchController::class, 'store']);
            Route::get('/{uuid}', [OfficeBranchController::class, 'show']);
            Route::put('/{uuid}', [OfficeBranchController::class, 'update']);
            Route::patch('/{uuid}', [OfficeBranchController::class, 'updateStatus']);
        });
        







        Route::get('testing', function (Request $request) {
            return $request->user();
        });

        // middleware example usage
        Route::group(['middleware' => ['permission:Read User', 'role:Admin']], function() {

            Route::get('test-permission', function () {
                return 'test-permission middleware';
            });



        });
        
    });

});
