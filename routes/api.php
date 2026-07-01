<?php

declare(strict_types=1);

use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');

Route::post('/payment/recurring-callback', [PaymentController::class, 'recurringCallback'])
    ->name('payment.recurring');

Route::post('/payment/pay-account-callback', [PaymentController::class, 'payAccountCallback'])
    ->name('payment.pay-account');
