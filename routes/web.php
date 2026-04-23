<?php

use App\Http\Controllers\FE\AuthControllerFE;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['throttle:10,1'],
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

});
