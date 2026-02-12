<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AdminCodeController;
use App\Http\Controllers\EnumerationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GatewaysController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminDashboardController;

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified']);

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('admin.dashboard');



Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/updateAllQRCodes', [EnumerationController::class, 'updateAllQRCodes'])->name('enumeration.updateAllQRCodes');

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/store', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::get('/{customer}/status', [CustomerController::class, 'toggleStatus'])->name('toggleStatus');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');

        // Extra routes for customer projects
        Route::get('/{customer}/projects', [CustomerController::class, 'projects'])->name('projects.index');
        Route::get('/{customer}/projects/create', [CustomerController::class, 'createProject'])->name('projects.create');
        Route::post('/{customer}/projects/store', [CustomerController::class, 'storeProject'])->name('projects.store');

        // Extra routes for customer staff
        Route::get('/{customer}/staff', [CustomerController::class, 'staff'])->name('staff.index');
        Route::get('/{customer}/staff/create', [CustomerController::class, 'createStaff'])->name('staff.create');
        Route::post('/{customer}/staff/store', [CustomerController::class, 'storeStaff'])->name('staff.store');
        Route::get('/{staff}/staff/show', [StaffController::class, 'show'])->name('staff.show');
        Route::put('/{staff}/staff', [StaffController::class, 'update'])->name('staff.update');
        Route::get('/{staff}/staff/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
        Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });

    // Project field routes
    Route::patch('fields/{field}/toggle', [ProjectController::class, 'toggleField'])
        ->name('fields.toggle');
    Route::delete('fields/{field}', [ProjectController::class, 'deleteField'])
        ->name('fields.delete');

    // Project routes
    Route::resource('projects', ProjectController::class);

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
            Route::put('location', [EnumerationController::class, 'location'])->name('enumeration.location.update');
            Route::put('/', [EnumerationController::class, 'update'])->name('enumeration.update');
            Route::delete('/', [EnumerationController::class, 'destroy'])->name('enumeration.destroy');
        });

        Route::patch('projects/enumerations/{enumeration}/verify', [EnumerationController::class, 'toggleVerification'])->name('enumeration.toggleVerification');
        Route::get('projects/{project}/export', [EnumerationController::class, 'export'])->name('enumeration.export');

        Route::get('codes/{project}', [AdminCodeController::class, 'index'])->name('codes.index');
        Route::get('codes/{project}/create', [AdminCodeController::class, 'create'])->name('codes.create');
        Route::post('codes/{project}/store', [AdminCodeController::class, 'storeBatch'])->name('codes.store');
        Route::get('codes/{project}/batches/{batch}', [AdminCodeController::class, 'showBatch'])->name('codes.show');
    });

    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->middleware('super_admin')->name('create');
        Route::post('/', [AdminController::class, 'store'])->middleware('super_admin')->name('store');
        Route::get('/{admin}', [AdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [AdminController::class, 'edit'])->middleware('super_admin')->name('edit');
        Route::patch('/{admin}', [AdminController::class, 'update'])->middleware('super_admin')->name('update');
        Route::patch('/{admin}/suspend', [AdminController::class, 'suspend'])->middleware('super_admin')->name('suspend');
        Route::patch('/{admin}/activate', [AdminController::class, 'activate'])->middleware('super_admin')->name('activate');
        Route::delete('/{admin}', [AdminController::class, 'destroy'])->middleware('super_admin')->name('destroy');
    });

    Route::prefix('gateways')->name('gateways.')->group(function () {
        Route::get('/', [GatewaysController::class, 'index'])->name('index');
        Route::get('/create', [GatewaysController::class, 'create'])->name('create');
        Route::post('/', [GatewaysController::class, 'store'])->name('store');
        Route::get('/{gateway}/edit', [GatewaysController::class, 'edit'])->name('edit');
        Route::patch('/{gateway}', [GatewaysController::class, 'update'])->name('update');
        Route::patch('/{gateway}/suspend', [GatewaysController::class, 'suspend'])->name('suspend');
        Route::patch('/{gateway}/activate', [GatewaysController::class, 'activate'])->name('activate');
        Route::delete('/{gateway}', [GatewaysController::class, 'destroy'])->middleware('super_admin')->name('destroy');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/staffAuth.php';
require __DIR__ . '/staff.php';
require __DIR__ . '/front.php';
