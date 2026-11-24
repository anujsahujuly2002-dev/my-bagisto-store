<?php

use EcomForYou\Admin\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

/**
 * Help & Resources routes.
 */
Route::controller(HelpController::class)->prefix('help')->group(function () {
    Route::get('', 'index')->name('admin.help.index');
});
