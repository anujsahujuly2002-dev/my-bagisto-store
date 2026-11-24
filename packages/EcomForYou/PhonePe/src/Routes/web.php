<?php

use EcomForYou\PhonePe\Http\Controllers\PhonePeController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'cart.buy_now'], 'prefix' => 'phonepe'], function () {
    Route::get('/redirect', [PhonePeController::class, 'redirect'])
        ->name('phonepe.redirect');

    Route::match(['get', 'post'], '/callback', [PhonePeController::class, 'callback'])
        ->withoutMiddleware(PreventRequestForgery::class)
        ->name('phonepe.callback');

    Route::get('/cancel', [PhonePeController::class, 'cancel'])
        ->name('phonepe.cancel');

    Route::post('/webhook', [PhonePeController::class, 'webhook'])
        ->withoutMiddleware(PreventRequestForgery::class)
        ->name('phonepe.webhook');
});
