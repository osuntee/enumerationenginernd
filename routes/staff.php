<?php

use App\Http\Controllers\Staff\ProfileController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\ProjectController;
use App\Http\Controllers\Staff\EnumerationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('staff.dashboard');
})->middleware(['staff'])->name('staff.dashboard');

Route::middleware('staff')->name('staff.')->group(function () {
    // Project routes
    Route::resource('projects', ProjectController::class);

    // Project field routes
    Route::patch('fields/{field}/toggle', [ProjectController::class, 'toggleField'])
        ->name('fields.toggle');
    Route::delete('fields/{field}', [ProjectController::class, 'deleteField'])
        ->name('fields.delete');

    // Additional project routes
    Route::prefix('projects')->name('projects.')->group(function () {
        // Staff management routes
        Route::get('/{project}/staff', [ProjectController::class, 'staff'])->name('staff.index');
        Route::get('/{project}/enumerations', [ProjectController::class, 'enumerations'])->name('enumerations.index');
        Route::post('/{project}/assign', [ProjectController::class, 'assign'])->name('staff.assign');
        Route::delete('/{project}/staff/{staff}/remove', [ProjectController::class, 'remove'])->name('staff.remove');

        // Payment management routes
        Route::get('{project}/payments', [ProjectController::class, 'payments'])->name('payments.index');
        Route::get('{project}/payments/create', [ProjectController::class, 'createPayment'])->name('payments.create');
        Route::post('{project}/payments/store', [ProjectController::class, 'storePayment'])->name('payments.store');
        Route::get('payments/{payment}/show', [ProjectController::class, 'showPayment'])->name('payments.show');
        Route::get('payments/{payment}/edit', [ProjectController::class, 'editPayment'])->name('payments.edit');
        Route::put('payments/{payment}', [ProjectController::class, 'updatePayment'])->name('payments.update');
        Route::patch('payments/{payment}/toggle-status', [ProjectController::class, 'togglePaymentStatus'])->name('payments.toggle');

        // Enumeration Payment management routes
        Route::get('payments/{payment}/enumerations', [ProjectController::class, 'enumerationPayments'])->name('payments.enumerations.index');
        Route::get('payments/{enumerationPayment}/enumerations/show', [ProjectController::class, 'showEnumerationPayment'])->name('payments.enumerations.show');
        Route::post('payments/{enumerationPayment}/enumerations/pay', [ProjectController::class, 'recordPayment'])->name('payments.enumerations.record-payment');

        // Field management routes
        Route::post('{project}/fields', [ProjectController::class, 'addField'])->name('addField');
        Route::patch('{project}/activate', [ProjectController::class, 'activate'])->name('activate');
        Route::patch('{project}/deactivate', [ProjectController::class, 'deactivate'])->name('deactivate');
        Route::post('{project}/field-order', [ProjectController::class, 'updateFieldOrder'])->name('updateFieldOrder');

        // Enumeration routes
        Route::get('{project}/enumerate', [EnumerationController::class, 'create'])->name('enumeration.create');
        Route::post('{project}/enumerate', [EnumerationController::class, 'store'])->name('enumeration.store');

        Route::prefix('enumerations/{enumeration}')->group(function () {
            Route::get('/', [EnumerationController::class, 'show'])->name('enumeration.show');
            Route::get('edit', [EnumerationController::class, 'edit'])->name('enumeration.edit');
            Route::put('/', [EnumerationController::class, 'update'])->name('enumeration.update');
            Route::put('location', [EnumerationController::class, 'location'])->name('enumeration.location.update');
            Route::delete('/', [EnumerationController::class, 'destroy'])->name('enumeration.destroy');
        });

        Route::patch('projects/enumerations/{enumeration}/verify', [EnumerationController::class, 'toggleVerification'])->name('enumeration.toggleVerification');
        Route::get('projects/{project}/export', [EnumerationController::class, 'export'])->name('enumeration.export');
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
