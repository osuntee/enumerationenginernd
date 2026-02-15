<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminProjectController;
use App\Http\Controllers\AdminCodeController;
use App\Http\Controllers\AdminEnumerationController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminGatewaysController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminDashboardController;

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified']);

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('admin.dashboard');



Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/updateAllQRCodes', [AdminEnumerationController::class, 'updateAllQRCodes'])->name('enumeration.updateAllQRCodes');

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index'])->name('index');
        Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
        Route::post('/store', [AdminCustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [AdminCustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('edit');
        Route::get('/{customer}/status', [AdminCustomerController::class, 'toggleStatus'])->name('toggleStatus');
        Route::put('/{customer}', [AdminCustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [AdminCustomerController::class, 'destroy'])->name('destroy');

        // Extra routes for customer projects
        Route::get('/{customer}/projects', [AdminCustomerController::class, 'projects'])->name('projects.index');
        Route::get('/{customer}/projects/create', [AdminCustomerController::class, 'createProject'])->name('projects.create');
        Route::post('/{customer}/projects/store', [AdminCustomerController::class, 'storeProject'])->name('projects.store');

        // Extra routes for customer staff
        Route::get('/{customer}/staff', [AdminCustomerController::class, 'staff'])->name('staff.index');
        Route::get('/{customer}/staff/create', [AdminCustomerController::class, 'createStaff'])->name('staff.create');
        Route::post('/{customer}/staff/store', [AdminCustomerController::class, 'storeStaff'])->name('staff.store');
        Route::get('/{staff}/staff/show', [AdminStaffController::class, 'show'])->name('staff.show');
        Route::put('/{staff}/staff', [AdminStaffController::class, 'update'])->name('staff.update');
        Route::get('/{staff}/staff/edit', [AdminStaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/{staff}/toggle-status', [AdminStaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
        Route::delete('/{staff}', [AdminStaffController::class, 'destroy'])->name('staff.destroy');
    });

    // Project field routes
    Route::patch('fields/{field}/toggle', [AdminProjectController::class, 'toggleField'])
        ->name('fields.toggle');
    Route::delete('fields/{field}', [AdminProjectController::class, 'deleteField'])
        ->name('fields.delete');

    // Project routes
    Route::resource('projects', AdminProjectController::class);

    // Additional project routes
    Route::prefix('projects')->name('projects.')->group(function () {
        // Staff management routes
        Route::get('/{project}/staff', [AdminProjectController::class, 'staff'])->name('staff.index');
        Route::get('/{project}/enumerations', [AdminProjectController::class, 'enumerations'])->name('enumerations.index');
        Route::post('/{project}/assign', [AdminProjectController::class, 'assign'])->name('staff.assign');
        Route::delete('/{project}/staff/{staff}/remove', [AdminProjectController::class, 'remove'])->name('staff.remove');

        // Payment management routes
        Route::get('{project}/payments', [AdminProjectController::class, 'payments'])->name('payments.index');
        Route::get('{project}/payments/create', [AdminProjectController::class, 'createPayment'])->name('payments.create');
        Route::post('{project}/payments/store', [AdminProjectController::class, 'storePayment'])->name('payments.store');
        Route::get('payments/{payment}/show', [AdminProjectController::class, 'showPayment'])->name('payments.show');
        Route::get('payments/{payment}/edit', [AdminProjectController::class, 'editPayment'])->name('payments.edit');
        Route::put('payments/{payment}', [AdminProjectController::class, 'updatePayment'])->name('payments.update');
        Route::patch('payments/{payment}/toggle-status', [AdminProjectController::class, 'togglePaymentStatus'])->name('payments.toggle');

        // Enumeration Payment management routes
        Route::get('payments/{payment}/enumerations', [AdminProjectController::class, 'enumerationPayments'])->name('payments.enumerations.index');
        Route::get('payments/{enumerationPayment}/enumerations/show', [AdminProjectController::class, 'showEnumerationPayment'])->name('payments.enumerations.show');
        Route::post('payments/{enumerationPayment}/enumerations/pay', [AdminProjectController::class, 'recordPayment'])->name('payments.enumerations.record-payment');

        // Field management routes
        Route::post('{project}/fields', [AdminProjectController::class, 'addField'])->name('addField');
        Route::patch('{project}/activate', [AdminProjectController::class, 'activate'])->name('activate');
        Route::patch('{project}/deactivate', [AdminProjectController::class, 'deactivate'])->name('deactivate');
        Route::post('{project}/field-order', [AdminProjectController::class, 'updateFieldOrder'])->name('updateFieldOrder');

        // Enumeration routes
        Route::get('{project}/enumerate', [AdminEnumerationController::class, 'create'])->name('enumeration.create');
        Route::post('{project}/enumerate', [AdminEnumerationController::class, 'store'])->name('enumeration.store');

        Route::prefix('enumerations/{enumeration}')->group(function () {
            Route::get('/', [AdminEnumerationController::class, 'show'])->name('enumeration.show');
            Route::get('edit', [AdminEnumerationController::class, 'edit'])->name('enumeration.edit');
            Route::put('location', [AdminEnumerationController::class, 'location'])->name('enumeration.location.update');
            Route::put('/', [AdminEnumerationController::class, 'update'])->name('enumeration.update');
            Route::delete('/', [AdminEnumerationController::class, 'destroy'])->name('enumeration.destroy');
        });

        Route::patch('projects/enumerations/{enumeration}/verify', [AdminEnumerationController::class, 'toggleVerification'])->name('enumeration.toggleVerification');
        Route::get('projects/{project}/export', [AdminEnumerationController::class, 'export'])->name('enumeration.export');

        Route::get('codes/{project}', [AdminCodeController::class, 'index'])->name('codes.index');
        Route::get('codes/{project}/create', [AdminCodeController::class, 'create'])->name('codes.create');
        Route::post('codes/{project}/store', [AdminCodeController::class, 'storeBatch'])->name('codes.store');
        Route::get('codes/{project}/batches/{batch}', [AdminCodeController::class, 'showBatch'])->name('codes.show');
        Route::get('codes/{project}/batches/{batch}/status', [AdminCodeController::class, 'checkStatus'])->name('codes.status');
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
        Route::get('/', [AdminGatewaysController::class, 'index'])->name('index');
        Route::get('/create', [AdminGatewaysController::class, 'create'])->name('create');
        Route::post('/', [AdminGatewaysController::class, 'store'])->name('store');
        Route::get('/{gateway}/edit', [AdminGatewaysController::class, 'edit'])->name('edit');
        Route::patch('/{gateway}', [AdminGatewaysController::class, 'update'])->name('update');
        Route::patch('/{gateway}/suspend', [AdminGatewaysController::class, 'suspend'])->name('suspend');
        Route::patch('/{gateway}/activate', [AdminGatewaysController::class, 'activate'])->name('activate');
        Route::delete('/{gateway}', [AdminGatewaysController::class, 'destroy'])->middleware('super_admin')->name('destroy');
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
