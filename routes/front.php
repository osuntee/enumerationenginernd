<?php

use App\Http\Controllers\Front\FrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('verify')->name('front.')->group(function () {
    Route::get('/{ref}', [FrontController::class, 'index'])->name('index');
    Route::get('/payment/{enumerationPayment}', [FrontController::class, 'viewPayment'])->name('payments.show');
    Route::post('/payment/{enumerationPayment}', [FrontController::class, 'processPayment'])->name('payments.process');

    // Paystack callback route
    Route::get('/paystack/callback', [FrontController::class, 'handlePaystackCallback'])->name('paystack.callback');

    // Flutterwave callback route
    Route::get('/flutterwave/callback', [FrontController::class, 'handleFlutterwaveCallback'])->name('flutterwave.callback');
});

Route::prefix('enumerate')->name('front.')->group(function () {
    Route::get('/{code}', [FrontController::class, 'enumerate'])->name('enumerate');
    Route::post('/{code}', [FrontController::class, 'storeEnumeration'])->name('enumerate.store');
});
