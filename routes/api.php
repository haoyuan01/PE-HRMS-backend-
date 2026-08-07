<?php

use App\Http\Controllers\BE\ActivityLogController;
use App\Http\Controllers\BE\AnnouncementController;
use App\Http\Controllers\BE\AnnouncementImageController;
use App\Http\Controllers\BE\AuthController;
use App\Http\Controllers\BE\ClaimHeaderController;
use App\Http\Controllers\BE\ClaimItemController;
use App\Http\Controllers\BE\ConfigurationController;
use App\Http\Controllers\BE\DepartmentController;
use App\Http\Controllers\BE\ErrorLogController;
use App\Http\Controllers\BE\LeaveEntitlementController;
use App\Http\Controllers\BE\LeavePolicyController;
use App\Http\Controllers\BE\LeaveRequestController;
use App\Http\Controllers\BE\LookupController;
use App\Http\Controllers\BE\MovementController;
use App\Http\Controllers\BE\MovementTypeController;
use App\Http\Controllers\BE\OfficeController;
use App\Http\Controllers\BE\OvertimeController;
use App\Http\Controllers\BE\PayrollController;
use App\Http\Controllers\BE\RoleController;
use App\Http\Controllers\BE\PositionController;
use App\Http\Controllers\BE\RequestLogController;
use App\Http\Controllers\BE\UpcomingEventController;
use App\Http\Controllers\BE\UpcomingEventImageController;
use App\Http\Controllers\BE\UserCertificateController;
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

    Route::get('/test', function () {
        return 'test uzumaki'; 
    });

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
            Route::get('manager-approvers', [LookupController::class, 'managerApprovers']);
            Route::get('roles', [LookupController::class, 'roles']);
            Route::get('departments', [LookupController::class, 'departments']);
            Route::get('positions', [LookupController::class, 'positions']);
            Route::get('offices', [LookupController::class, 'offices']);
            Route::get('movement-types', [LookupController::class, 'movementTypes']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{uuid}', [UserController::class, 'show']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{uuid}', [UserController::class, 'update']);
            Route::patch('/{uuid}', [UserController::class, 'updateStatus']);
            Route::patch('/{uuid}/password', [UserController::class, 'updatePassword']);
            Route::patch('/{uuid}/passcode', [UserController::class, 'updatePasscode']);
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

        Route::prefix('user-certificates')->group(function () {
            Route::get('/', [UserCertificateController::class, 'index']);
            Route::post('/', [UserCertificateController::class, 'store']);
            Route::get('/{uuid}', [UserCertificateController::class, 'show']);
            Route::put('/{uuid}', [UserCertificateController::class, 'update']);
            Route::patch('/{uuid}', [UserCertificateController::class, 'updateStatus']);
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

        Route::prefix('leave-policies')->group(function () {
            Route::get('/', [LeavePolicyController::class, 'index']);
            Route::post('/', [LeavePolicyController::class, 'store']);
            Route::get('/{uuid}', [LeavePolicyController::class, 'show']);
            Route::put('/{uuid}', [LeavePolicyController::class, 'update']);
            Route::patch('/{uuid}', [LeavePolicyController::class, 'updateStatus']);
        });

        Route::prefix('leave-entitlements')->group(function () {
            Route::get('/', [LeaveEntitlementController::class, 'index']);
            Route::get('/{uuid}', [LeaveEntitlementController::class, 'show']);
            Route::put('/{uuid}', [LeaveEntitlementController::class, 'update']);
        });

        Route::prefix('leave-requests')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index']);
            Route::get('/calendar-summaries', [LeaveRequestController::class, 'calendarSummaries']);
            Route::get('/status-summaries', [LeaveRequestController::class, 'statusSummaries']);
            Route::post('/', [LeaveRequestController::class, 'store']);
            Route::get('/{uuid}', [LeaveRequestController::class, 'show']);
            Route::put('/{uuid}', [LeaveRequestController::class, 'update']);
            Route::patch('/manager-approves/{uuid}', [LeaveRequestController::class, 'managerApprove']);
            Route::patch('/director-approves/{uuid}', [LeaveRequestController::class, 'directorApprove']);
            Route::patch('/{uuid}', [LeaveRequestController::class, 'updateStatus']);
        });

        Route::prefix('overtimes')->group(function () {
            Route::get('/', [OvertimeController::class, 'index']);
            Route::post('/', [OvertimeController::class, 'store']);
            Route::get('/{uuid}', [OvertimeController::class, 'show']);
            Route::patch('/director-approves/{uuid}', [OvertimeController::class, 'directorApprove']);
            Route::patch('/{uuid}', [OvertimeController::class, 'updateStatus']);
        });

        Route::prefix('payrolls')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::post('/{uuid}', [PayrollController::class, 'show']);
            Route::put('/{uuid}', [PayrollController::class, 'update']);
            Route::patch('/{uuid}', [PayrollController::class, 'updateStatus']);
        });

        Route::prefix('claim-headers')->group(function () {
            Route::get('/', [ClaimHeaderController::class, 'index']);
            Route::post('/', [ClaimHeaderController::class, 'store']);
            Route::get('/{uuid}', [ClaimHeaderController::class, 'show']);
            Route::put('/{uuid}', [ClaimHeaderController::class, 'update']);
            Route::patch('/manager-reviews/{uuid}', [ClaimHeaderController::class, 'managerReview']);
            Route::patch('/director-reviews/{uuid}', [ClaimHeaderController::class, 'directorReview']);
            Route::patch('/{uuid}', [ClaimHeaderController::class, 'updateStatus']);
        });

        Route::prefix('claim-items')->group(function () {
            Route::patch('/manager-approves/{uuid}', [ClaimItemController::class, 'managerApprove']);
            Route::patch('/director-approves/{uuid}', [ClaimItemController::class, 'directorApprove']);
        });

        Route::prefix('movement-types')->group(function () {
            Route::get('/', [MovementTypeController::class, 'index']);
            Route::post('/', [MovementTypeController::class, 'store']);
            Route::get('/{uuid}', [MovementTypeController::class, 'show']);
            Route::put('/{uuid}', [MovementTypeController::class, 'update']);
            Route::patch('/{uuid}', [MovementTypeController::class, 'updateStatus']);
        });

        Route::prefix('movements')->group(function () {
            Route::get('/', [MovementController::class, 'index']);
            Route::get('/calendar-summaries', [MovementController::class, 'calendarSummaries']);
            Route::post('/', [MovementController::class, 'store']);
            Route::get('/{uuid}', [MovementController::class, 'show']);
            Route::put('/{uuid}', [MovementController::class, 'update']);
            Route::patch('/{uuid}', [MovementController::class, 'updateStatus']);
        });








        // permission middleware example usage
        Route::group(['middleware' => ['permission:Read User', 'role:Admin']], function() {

            Route::get('test-permission', function () {
                return 'test-permission middleware';
            });

        });
        
    });

});
