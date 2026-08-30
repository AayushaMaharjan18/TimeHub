<?php

use App\Http\Controllers\Payments\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Payment gateway browser-redirect callbacks. These perform the authoritative
// server-side verification, then redirect the browser to the frontend result
// page with a plain status flag — the frontend never decides success itself.
Route::get('/payments/esewa/callback', [PaymentCallbackController::class, 'esewa'])->name('payments.esewa.callback');
Route::get('/payments/khalti/callback', [PaymentCallbackController::class, 'khalti'])->name('payments.khalti.callback');
