<?php

use EcomForYou\SocialLogin\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::controller(LoginController::class)->middleware(['web', 'shop'])->prefix('customer/social-login/{provider}')->group(function () {
    Route::get('', 'redirectToProvider')->name('customer.social_login.index');

    Route::get('callback', 'handleProviderCallback')->name('customer.social_login.callback');
});
