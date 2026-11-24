<?php

use EcomForYou\Razorpay\Http\Controllers\RazorpayController;
use Illuminate\Support\Facades\Route;

Route::controller(RazorpayController::class)
    ->middleware(['web', 'cart.buy_now'])
    ->prefix('razorpay/payment')
    ->group(function () {
        Route::get('redirect', 'redirect')->name('razorpay.payment.redirect');

        Route::get('success', 'paymentSuccess')->name('razorpay.payment.success');

        Route::get('fail', 'paymentFail')->name('razorpay.payment.cancel');
    });
