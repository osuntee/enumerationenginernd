<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Staff\Mobile\AuthController;
use App\Http\Controllers\Staff\Mobile\HomeController;
use App\Http\Controllers\Staff\Mobile\ProjectController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset', [AuthController::class, 'resetPass']);
Route::post('/verify/otp', [AuthController::class, 'verifyOTP']);
Route::post('/resend/otp', [AuthController::class, 'resendOTP']);

//Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/set/password', [AuthController::class, 'setPassword']);
    Route::post('/update/password', [AuthController::class, 'updatePassword']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/home', [HomeController::class, 'index']);



    Route::prefix('project')->group(function () {
        Route::get('{id}', [ProjectController::class, 'index']);
        Route::post('{id}/enumerate', [ProjectController::class, 'enumerate']);
        Route::get('{id}/records', [ProjectController::class, 'records']);
    });
});
