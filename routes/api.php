<?php

use App\Constants\HttpStatusCodeConstants;
use App\Http\Controllers\BE\ActivityLogController;
use App\Http\Controllers\BE\AnnouncementController;
use App\Http\Controllers\BE\AnnouncementImageController;
use App\Http\Controllers\BE\AuthController;
use App\Http\Controllers\BE\ClaimHeaderController;
use App\Http\Controllers\BE\ConfigurationController;
use App\Http\Controllers\BE\DepartmentController;
use App\Http\Controllers\BE\ErrorLogController;
use App\Http\Controllers\BE\LookupController;
use App\Http\Controllers\BE\OfficeController;
use App\Http\Controllers\BE\RoleController;
use App\Http\Controllers\BE\PositionController;
use App\Http\Controllers\BE\RequestLogController;
use App\Http\Controllers\BE\UpcomingEventController;
use App\Http\Controllers\BE\UpcomingEventImageController;
use App\Http\Controllers\BE\UserContactController;
use App\Http\Controllers\BE\UserController;
use App\Http\Controllers\BE\UserEmergencyController;
use App\Http\Controllers\BE\UserEmploymentController;
use App\Http\Controllers\BE\UserPersonalController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

$version = 'v1';

Route::group([
    'prefix' => $version,
    'middleware' => ['throttle:auth', 'log.request'],
], function () {

    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password-email', [AuthController::class, 'forgotPasswordEmail']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function ($router) {

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('forgot-passcode-email', [AuthController::class, 'forgotPasscodeEmail']);
        });

        Route::prefix('activity-logs')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index']);
            Route::get('/{uuid}', [ActivityLogController::class, 'show']);
        });

        Route::prefix('request-logs')->group(function () {
            Route::get('/', [RequestLogController::class, 'index']);
            Route::get('/{uuid}', [RequestLogController::class, 'show']);
        });

        Route::prefix('error-logs')->group(function () {
            Route::get('/', [ErrorLogController::class, 'index']);
            Route::get('/{uuid}', [ErrorLogController::class, 'show']);
        });

        Route::prefix('configurations')->group(function () {
            Route::get('/', [ConfigurationController::class, 'index']);
            Route::get('/{uuid}', [ConfigurationController::class, 'show']);
            Route::put('/{uuid}', [ConfigurationController::class, 'update']);
        });

        Route::prefix('lookup')->group(function () {
            Route::get('permissions', [LookupController::class, 'permissions']);
            Route::get('users', [LookupController::class, 'users']);
            Route::get('claim-approvers', [LookupController::class, 'claimApprovers']);
            Route::get('roles', [LookupController::class, 'roles']);
            Route::get('departments', [LookupController::class, 'departments']);
            Route::get('positions', [LookupController::class, 'positions']);
            Route::get('offices', [LookupController::class, 'offices']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{uuid}', [UserController::class, 'show']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{uuid}', [UserController::class, 'update']);
            Route::patch('/{uuid}', [UserController::class, 'updateStatus']);
            Route::patch('/{uuid}/password', [UserController::class, 'updatePassword']);
        });

        Route::prefix('user-contacts')->group(function () {
            Route::put('/', [UserContactController::class, 'update']);
        });

        Route::prefix('user-emergencies')->group(function () {
            Route::put('/', [UserEmergencyController::class, 'update']);
        });

        Route::prefix('user-employments')->group(function () {
            Route::put('/', [UserEmploymentController::class, 'update']);
        });

        Route::prefix('user-personals')->group(function () {
            Route::put('/', [UserPersonalController::class, 'update']);
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

        Route::prefix('offices')->group(function () {
            Route::get('/', [OfficeController::class, 'index']);
            Route::post('/', [OfficeController::class, 'store']);
            Route::get('/{uuid}', [OfficeController::class, 'show']);
            Route::put('/{uuid}', [OfficeController::class, 'update']);
            Route::patch('/{uuid}', [OfficeController::class, 'updateStatus']);
        });

        Route::prefix('announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index']);
            Route::post('/', [AnnouncementController::class, 'store']);
            Route::get('/{uuid}', [AnnouncementController::class, 'show']);
            Route::put('/{uuid}', [AnnouncementController::class, 'update']);
            Route::patch('/{uuid}', [AnnouncementController::class, 'updateStatus']);
        });

        Route::prefix('announcement-images')->group(function () {
            Route::patch('/{uuid}', [AnnouncementImageController::class, 'updateStatus']);
        });

        Route::prefix('upcoming-events')->group(function () {
            Route::get('/', [UpcomingEventController::class, 'index']);
            Route::post('/', [UpcomingEventController::class, 'store']);
            Route::get('/{uuid}', [UpcomingEventController::class, 'show']);
            Route::put('/{uuid}', [UpcomingEventController::class, 'update']);
            Route::patch('/{uuid}', [UpcomingEventController::class, 'updateStatus']);
        });

        Route::prefix('upcoming-event-images')->group(function () {
            Route::patch('/{uuid}', [UpcomingEventImageController::class, 'updateStatus']);
        });

        Route::prefix('claim-headers')->group(function () {
            Route::get('/', [ClaimHeaderController::class, 'index']);
            Route::post('/', [ClaimHeaderController::class, 'store']);
            Route::get('/{uuid}', [ClaimHeaderController::class, 'show']);
            Route::put('/{uuid}', [ClaimHeaderController::class, 'update']);
            Route::patch('/{uuid}', [ClaimHeaderController::class, 'updateStatus']);
            Route::patch('/{uuid}/approve', [ClaimHeaderController::class, 'approve']);
            Route::patch('/{uuid}/paid', [ClaimHeaderController::class, 'paid']);
            Route::patch('/{uuid}/reject', [ClaimHeaderController::class, 'reject']);
        });












        // permission middleware example usage
        Route::group(['middleware' => ['permission:Read User', 'role:Admin']], function() {

            Route::get('test-permission', function () {
                return 'test-permission middleware';
            });

        });
        
    });

});
