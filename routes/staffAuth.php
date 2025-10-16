<?php

use App\Http\Controllers\Staff\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Staff\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Staff\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Staff\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Staff\Auth\NewPasswordController;
use App\Http\Controllers\Staff\Auth\PasswordController;
use App\Http\Controllers\Staff\Auth\PasswordResetLinkController;
use App\Http\Controllers\Staff\Auth\RegisteredStaffController;
use App\Http\Controllers\Staff\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Guest routes for staff (not authenticated)
Route::middleware('guest:staff')->group(function () {
    // Staff Registration (if needed - you might want to restrict this)
    Route::get('register', [RegisteredStaffController::class, 'create'])
        ->name('staff.register');
    Route::post('register', [RegisteredStaffController::class, 'store']);

    // Staff Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('staff.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('staff.login.store');

    // Staff Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('staff.password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('staff.password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('staff.password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('staff.password.store');
});

// Authenticated staff routes
Route::middleware('auth:staff')->group(function () {
    // Email Verification
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('staff.verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('staff.verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('staff.verification.send');

    // Password Confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('staff.password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->name('staff.password.confirm.store');

    // Password Update
    Route::put('password', [PasswordController::class, 'update'])
        ->name('staff.password.update');

    // Staff Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('staff.logout');
});
