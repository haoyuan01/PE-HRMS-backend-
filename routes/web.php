<?php

use App\Http\Controllers\FE\AuthControllerFE;
use App\Http\Controllers\FE\LeaveRequestControllerFE;
use App\Http\Controllers\FE\OvertimeControllerFE;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['throttle:10,1', 'log.request'],
], function () {

    Route::get('/', function () {
        return response()->json([
            'service' => 'PE Portal API',
            'status' => 'running',
            'version' => '1.0.0',
        ]);
    });

    Route::get('/reset-password', [AuthControllerFE::class, 'resetPassword']);
    Route::get('/reset-password-success', [AuthControllerFE::class, 'resetPasswordSuccess']);
    Route::post('/reset-password', [AuthControllerFE::class, 'resetPasswordAction']);
    Route::get('/reset-passcode', [AuthControllerFE::class, 'resetPasscode']);
    Route::get('/reset-passcode-success', [AuthControllerFE::class, 'resetPasscodeSuccess']);
    Route::post('/reset-passcode', [AuthControllerFE::class, 'resetPasscodeAction']);

    Route::get('/leave-request-review', [LeaveRequestControllerFE::class, 'approveLeave']);
    Route::post('/leave-request-review', [LeaveRequestControllerFE::class, 'approveLeaveAction']);
    Route::get('/leave-request-review-success', [LeaveRequestControllerFE::class, 'approveLeaveSuccess']);
    
    Route::get('/overtime-review', [OvertimeControllerFE::class, 'review']);
    Route::post('/overtime-review', [OvertimeControllerFE::class, 'reviewAction']);
    Route::get('/overtime-review-success', [OvertimeControllerFE::class, 'reviewSuccess']);

});
