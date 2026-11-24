<?php

use EcomForYou\Admin\Http\Controllers\Appearance\SectionController;
use EcomForYou\Admin\Http\Controllers\Appearance\ThemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('appearance')->group(function () {
    /**
     * Theme gallery routes.
     */
    Route::controller(ThemeController::class)->prefix('themes')->group(function () {
        Route::get('', 'index')->name('admin.appearance.themes.index');

        Route::post('upload', 'upload')->name('admin.appearance.themes.upload');

        Route::get('upload/{id}', 'uploadStatus')->name('admin.appearance.themes.upload_status');

        Route::post('schedules', 'storeSchedule')->name('admin.appearance.themes.schedules.store');

        Route::delete('schedules/{id}', 'destroySchedule')->name('admin.appearance.themes.schedules.delete');

        Route::post('preview/stop', 'stopPreview')->name('admin.appearance.themes.stop_preview');

        Route::post('{code}/preview', 'preview')->name('admin.appearance.themes.preview');

        Route::get('{code}/impact', 'impact')->name('admin.appearance.themes.impact');

        Route::post('{code}/activate', 'activate')->name('admin.appearance.themes.activate');

        Route::post('{code}/rollback', 'rollback')->name('admin.appearance.themes.rollback');

        Route::get('{code}/images', 'images')->name('admin.appearance.themes.images.index');

        Route::post('{code}/images/{image}', 'updateImage')->name('admin.appearance.themes.images.update');

        Route::delete('{code}/images/{image}', 'destroyImage')->name('admin.appearance.themes.images.delete');

        Route::delete('{code}', 'destroy')->name('admin.appearance.themes.delete');
    });

    /**
     * Section routes. A section belongs to a theme, so the listing and the create
     * endpoint hang off the theme; everything else is addressed by section id.
     */
    Route::controller(SectionController::class)->group(function () {
        Route::get('themes/{code}/sections', 'index')->name('admin.appearance.sections.index');

        Route::post('themes/{code}/sections', 'store')->name('admin.appearance.sections.store');

        Route::post('themes/{code}/sections/publish', 'publish')->name('admin.appearance.sections.publish');

        Route::post('themes/{code}/sections/discard', 'discard')->name('admin.appearance.sections.discard');

        Route::prefix('sections')->group(function () {
            Route::post('edit/{id}', 'update')->name('admin.appearance.sections.update');

            Route::delete('edit/{id}', 'destroy')->name('admin.appearance.sections.delete');

            Route::post('reorder', 'reorder')->name('admin.appearance.sections.reorder');

            Route::get('{id}/fields', 'fields')->name('admin.appearance.sections.fields');

            Route::post('{id}/draft', 'saveDraft')->name('admin.appearance.sections.draft');

            Route::post('{id}/duplicate', 'duplicate')->name('admin.appearance.sections.duplicate');

            Route::post('{id}/media', 'uploadMedia')->name('admin.appearance.sections.media');

            Route::post('{id}/status', 'status')->name('admin.appearance.sections.status');
        });
    });
});
