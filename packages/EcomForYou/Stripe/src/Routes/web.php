<?php

use EcomForYou\Stripe\Http\Controllers\StripeController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::controller(StripeController::class)
    ->middleware(['web', 'cart.buy_now'])
    ->prefix('stripe')
    ->group(function () {
        Route::get('redirect', 'redirect')->name('stripe.standard.redirect');

        Route::get('success', 'success')->name('stripe.payment.success');

        Route::get('cancel', 'cancel')->name('stripe.payment.cancel');

        Route::post('webhook', 'webhook')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->name('stripe.webhook');
    });
