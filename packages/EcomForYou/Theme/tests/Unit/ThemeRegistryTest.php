<?php

use EcomForYou\Theme\Models\Theme;
use EcomForYou\Theme\Repositories\ThemeRepository;
use EcomForYou\Theme\ThemeCatalog;
use EcomForYou\Theme\ThemeRegistry;
use EcomForYou\Theme\Themes;
use Illuminate\Support\Facades\Cache;

/**
 * Store a theme row the way an upload registers one.
 */
function registerTheme(array $attributes = []): Theme
{
    return app(ThemeRepository::class)->create(array_merge([
        'code' => 'uploaded',
        'panel' => Theme::PANEL_SHOP,
        'name' => 'Uploaded',
        'version' => '1.0.0',
        'parent' => 'default',
        'assets_path' => 'storage/app/public/themes/uploaded',
        'views_path' => 'themes/uploaded/views',
        'source' => Theme::SOURCE_UPLOAD,
        'status' => true,
    ], $attributes));
}

// ============================================================================
// Merging Into Config
// ============================================================================

it('should register a stored storefront theme alongside the configured ones', function () {
    registerTheme();

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.uploaded'))->toMatchArray([
        'name' => 'Uploaded',
        'parent' => 'default',
        'assets_path' => 'storage/app/public/themes/uploaded',
        'views_path' => 'storage/app/themes/uploaded/views',
    ])->and(config('themes.shop.default'))->not->toBeNull();
});

it('should register a theme that was not uploaded with its views path as stored', function () {
    registerTheme(['source' => 'catalog', 'views_path' => 'resources/themes/uploaded/views']);

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.uploaded.views_path'))->toBe('resources/themes/uploaded/views');
});

it('should put back the configured themes alone when restored', function () {
    registerTheme();

    $registry = app(ThemeRegistry::class);

    $registry->merge();

    expect(config('themes.shop.uploaded'))->not->toBeNull();

    $registry->restore();

    expect(config('themes.shop.uploaded'))->toBeNull()
        ->and(config('themes.shop.default'))->not->toBeNull();
});

it('should register a stored admin theme under the admin panel only', function () {
    registerTheme(['code' => 'dark-admin', 'panel' => Theme::PANEL_ADMIN, 'name' => 'Dark Admin']);

    app(ThemeRegistry::class)->merge();

    expect(config('themes.admin.dark-admin.name'))->toBe('Dark Admin')
        ->and(config('themes.shop.dark-admin'))->toBeNull();
});

it('should keep the configured theme when a stored one shares its code', function () {
    registerTheme(['code' => 'default', 'name' => 'Impostor']);

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.default.name'))->toBe('Default');
});

it('should leave a disabled theme unregistered', function () {
    registerTheme(['status' => false]);

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.uploaded'))->toBeNull();
});

it('should resolve a stored theme with its parent', function () {
    registerTheme();

    app(ThemeRegistry::class)->merge();

    $themes = new Themes;

    expect($themes->exists('uploaded'))->toBeTrue()
        ->and($themes->find('uploaded')->getParent()?->code)->toBe('default');
});

// ============================================================================
// Cache
// ============================================================================

it('should forget the cached registry when a theme is saved or deleted', function () {
    $theme = registerTheme();

    app(ThemeRegistry::class)->merge();

    expect(Cache::has(ThemeRegistry::CACHE_KEY))->toBeTrue();

    $theme->update(['name' => 'Renamed']);

    expect(Cache::has(ThemeRegistry::CACHE_KEY))->toBeFalse();

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.uploaded.name'))->toBe('Renamed');

    $theme->delete();

    expect(Cache::has(ThemeRegistry::CACHE_KEY))->toBeFalse();

    app(ThemeRegistry::class)->merge();

    expect(config('themes.shop.uploaded'))->toBeNull()
        ->and(config('themes.shop.default'))->not->toBeNull();
});

// ============================================================================
// Gallery
// ============================================================================

it('should list a stored theme in the gallery as installed', function () {
    registerTheme();

    app(ThemeRegistry::class)->merge();

    $theme = app(ThemeCatalog::class)->find('uploaded');

    expect($theme['is_installed'])->toBeTrue()
        ->and($theme['status'])->toBe(ThemeCatalog::STATUS_INSTALLED)
        ->and($theme['name'])->toBe('Uploaded');
});
