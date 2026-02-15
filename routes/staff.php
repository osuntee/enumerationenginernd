<?php

use App\Http\Controllers\Staff\StaffProfileController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\StaffProjectController;
use App\Http\Controllers\Staff\StaffEnumerationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Staff\StaffDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [StaffDashboardController::class, 'index'])
    ->middleware(['staff'])->name('staff.dashboard');

Route::middleware('staff')->name('staff.')->group(function () {
    // Project routes
    Route::resource('projects', StaffProjectController::class);

    // Project field routes
    Route::patch('fields/{field}/toggle', [StaffProjectController::class, 'toggleField'])
        ->name('fields.toggle');
    Route::delete('fields/{field}', [StaffProjectController::class, 'deleteField'])
        ->name('fields.delete');

    // Additional project routes
    Route::prefix('projects')->name('projects.')->group(function () {
        // Staff management routes
        Route::get('/{project}/staff', [StaffProjectController::class, 'staff'])->name('staff.index');
        Route::get('/{project}/enumerations', [StaffProjectController::class, 'enumerations'])->name('enumerations.index');
        Route::post('/{project}/assign', [StaffProjectController::class, 'assign'])->name('staff.assign');
        Route::delete('/{project}/staff/{staff}/remove', [StaffProjectController::class, 'remove'])->name('staff.remove');

        // Payment management routes
        Route::get('{project}/payments', [StaffProjectController::class, 'payments'])->name('payments.index');
        Route::get('{project}/payments/create', [StaffProjectController::class, 'createPayment'])->name('payments.create');
        Route::post('{project}/payments/store', [StaffProjectController::class, 'storePayment'])->name('payments.store');
        Route::get('payments/{payment}/show', [StaffProjectController::class, 'showPayment'])->name('payments.show');
        Route::get('payments/{payment}/edit', [StaffProjectController::class, 'editPayment'])->name('payments.edit');
        Route::put('payments/{payment}', [StaffProjectController::class, 'updatePayment'])->name('payments.update');
        Route::patch('payments/{payment}/toggle-status', [StaffProjectController::class, 'togglePaymentStatus'])->name('payments.toggle');

        // Enumeration Payment management routes
        Route::get('payments/{payment}/enumerations', [StaffProjectController::class, 'enumerationPayments'])->name('payments.enumerations.index');
        Route::get('payments/{enumerationPayment}/enumerations/show', [StaffProjectController::class, 'showEnumerationPayment'])->name('payments.enumerations.show');
        Route::post('payments/{enumerationPayment}/enumerations/pay', [StaffProjectController::class, 'recordPayment'])->name('payments.enumerations.record-payment');

        // Field management routes
        Route::post('{project}/fields', [StaffProjectController::class, 'addField'])->name('addField');
        Route::patch('{project}/activate', [StaffProjectController::class, 'activate'])->name('activate');
        Route::patch('{project}/deactivate', [StaffProjectController::class, 'deactivate'])->name('deactivate');
        Route::post('{project}/field-order', [StaffProjectController::class, 'updateFieldOrder'])->name('updateFieldOrder');

        // Enumeration routes
        Route::get('{project}/enumerate', [StaffEnumerationController::class, 'create'])->name('enumeration.create');
        Route::post('{project}/enumerate', [StaffEnumerationController::class, 'store'])->name('enumeration.store');

        Route::prefix('enumerations/{enumeration}')->group(function () {
            Route::get('/', [StaffEnumerationController::class, 'show'])->name('enumeration.show');
            Route::get('edit', [StaffEnumerationController::class, 'edit'])->name('enumeration.edit');
            Route::put('/', [StaffEnumerationController::class, 'update'])->name('enumeration.update');
            Route::put('location', [StaffEnumerationController::class, 'location'])->name('enumeration.location.update');
            Route::delete('/', [StaffEnumerationController::class, 'destroy'])->name('enumeration.destroy');
        });

        Route::patch('projects/enumerations/{enumeration}/verify', [StaffEnumerationController::class, 'toggleVerification'])->name('enumeration.toggleVerification');
        Route::get('projects/{project}/export', [StaffEnumerationController::class, 'export'])->name('enumeration.export');
    });

    // Staff management routes
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/store', [StaffController::class, 'store'])->name('store');
        Route::get('/{staff}/show', [StaffController::class, 'show'])->name('show');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
        Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::patch('/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggleStatus');
        Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
    });


    // Profile routes
    Route::get('/profile', [StaffProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [StaffProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [StaffProfileController::class, 'destroy'])->name('profile.destroy');
});
