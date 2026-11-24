<?php

use EcomForYou\Admin\Http\Controllers\CommandPaletteController;
use EcomForYou\Admin\Http\Controllers\DashboardController;
use EcomForYou\Admin\Http\Controllers\DataGrid\DataGridController;
use EcomForYou\Admin\Http\Controllers\DataGrid\SavedFilterController;
use EcomForYou\Admin\Http\Controllers\MagicAIController;
use EcomForYou\Admin\Http\Controllers\TinyMCEController;
use EcomForYou\Admin\Http\Controllers\User\AccountController;
use EcomForYou\Admin\Http\Controllers\User\SessionController;
use EcomForYou\Admin\Http\Controllers\User\TwoFactorController;
use Illuminate\Support\Facades\Route;

/**
 * Dashboard routes.
 */
Route::controller(DashboardController::class)->prefix('dashboard')->group(function () {
    Route::get('', 'index')->name('admin.dashboard.index');

    Route::get('stats', 'stats')->name('admin.dashboard.stats');
});

/**
 * Datagrid routes.
 */
Route::controller(DataGridController::class)->prefix('datagrid')->group(function () {
    Route::get('look-up', 'lookUp')->name('admin.datagrid.look_up');

    Route::controller(SavedFilterController::class)->prefix('saved-filters')->group(function () {
        Route::post('', 'store')->name('admin.datagrid.saved_filters.store');

        Route::get('', 'get')->name('admin.datagrid.saved_filters.index');

        Route::put('{id}', 'update')->name('admin.datagrid.saved_filters.update');

        Route::delete('{id}', 'destroy')->name('admin.datagrid.saved_filters.destroy');
    });
});

/**
 * Command palette routes.
 */
Route::get('command-palette', [CommandPaletteController::class, 'index'])->name('admin.command_palette.index');

/**
 * Tinymce file upload handler.
 */
Route::post('tinymce/upload', [TinyMCEController::class, 'upload'])->name('admin.tinymce.upload');

/**
 * AI Routes
 */
Route::controller(MagicAIController::class)->prefix('magic-ai')->group(function () {
    Route::post('content', 'content')->name('admin.magic_ai.content');

    Route::post('image', 'image')->name('admin.magic_ai.image');
});

/**
 * Admin profile routes.
 */
Route::controller(AccountController::class)->prefix('account')->group(function () {
    Route::get('', 'edit')->name('admin.account.edit');

    Route::put('', 'update')->name('admin.account.update');
});

/**
 * Admin two-factor authentication routes.
 */
Route::controller(TwoFactorController::class)->prefix('two-factor')->group(function () {
    Route::get('setup', 'setup')->name('admin.two_factor.setup');

    Route::post('enable', 'enable')->middleware('throttle:5,1')->name('admin.two_factor.enable');

    Route::post('disable', 'disable')->name('admin.two_factor.disable');
});

Route::delete('logout', [SessionController::class, 'destroy'])->name('admin.session.destroy');
