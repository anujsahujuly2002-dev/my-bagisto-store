<?php

use EcomForYou\Theme\Models\Theme;
use EcomForYou\Theme\Repositories\ThemeRepository;

/**
 * Load the migration that moves uploaded theme views paths under the store's own folder.
 */
function storeFolderMigration(): object
{
    return require base_path('packages/EcomForYou/Theme/src/Database/Migrations/2026_11_24_000001_store_uploaded_theme_views_under_store_folder.php');
}

// ============================================================================
// Uploaded Theme Views Paths
// ============================================================================

it('should store an uploaded theme views path relative to the store folder and back', function () {
    $theme = app(ThemeRepository::class)->create([
        'code' => 'migrated-upload',
        'panel' => Theme::PANEL_SHOP,
        'name' => 'Migrated Upload',
        'assets_path' => 'themes/migrated-upload/assets/1',
        'views_path' => 'storage/app/themes/migrated-upload/releases/1/views',
        'source' => Theme::SOURCE_UPLOAD,
    ]);

    storeFolderMigration()->up();

    expect($theme->refresh()->views_path)->toBe('themes/migrated-upload/releases/1/views')
        ->and($theme->viewsPathFromBase())->toBe('storage/app/themes/migrated-upload/releases/1/views');

    storeFolderMigration()->down();

    expect($theme->refresh()->views_path)->toBe('storage/app/themes/migrated-upload/releases/1/views');
});

it('should leave a theme that was not uploaded as it is', function () {
    $theme = app(ThemeRepository::class)->create([
        'code' => 'migrated-catalog',
        'panel' => Theme::PANEL_SHOP,
        'name' => 'Migrated Catalog',
        'assets_path' => 'themes/migrated-catalog',
        'views_path' => 'storage/app/themes/migrated-catalog/views',
        'source' => 'catalog',
    ]);

    storeFolderMigration()->up();

    expect($theme->refresh()->views_path)->toBe('storage/app/themes/migrated-catalog/views');
});
