<?php

use EcomForYou\FPC\CacheProfiles\FullPageCacheProfile;
use EcomForYou\Theme\ThemePreview;
use EcomForYou\Theme\Themes;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withSession;

beforeEach(function () {
    $this->previewCode = 'preview-'.Str::lower(Str::random(10));

    $this->previewViews = 'storage/framework/testing/'.$this->previewCode.'/views';

    File::ensureDirectoryExists(base_path($this->previewViews.'/home'));

    File::put(base_path($this->previewViews.'/home/index.blade.php'), "<x-shop::layouts>\n    <p>Preview Theme Home</p>\n</x-shop::layouts>\n");

    config(['themes.shop.'.$this->previewCode => [
        'name' => 'Preview Theme',
        'parent' => 'default',
        'assets_path' => 'public/themes/shop/default',
        'views_path' => $this->previewViews,
    ]]);
});

afterEach(function () {
    File::deleteDirectory(base_path('storage/framework/testing/'.$this->previewCode));
});

/**
 * Rebuild the theme registry the way a fresh storefront request does, after an admin request built it for the panel.
 */
function freshThemes(): void
{
    app()->forgetInstance(Themes::class);

    Facade::clearResolvedInstance(Themes::class);
}

// ============================================================================
// Starting And Stopping
// ============================================================================

it('should start a preview and send the admin to the storefront', function () {
    $this->loginAsAdmin();

    post(route('admin.appearance.themes.preview', $this->previewCode))
        ->assertRedirect(route('shop.home.index'))
        ->assertSessionHas(ThemePreview::SESSION_KEY, $this->previewCode);
});

it('should refuse to preview a theme that is not installed', function () {
    $this->loginAsAdmin();

    post(route('admin.appearance.themes.preview', 'not-installed'))->assertNotFound();
});

it('should stop a preview and send the admin back to the storefront', function () {
    $this->loginAsAdmin();

    withSession([ThemePreview::SESSION_KEY => $this->previewCode])
        ->post(route('admin.appearance.themes.stop_preview'))
        ->assertRedirect(route('shop.home.index'))
        ->assertSessionMissing(ThemePreview::SESSION_KEY);
});

it('should refuse a preview to an admin who cannot see the theme gallery', function () {
    $this->loginAsAdminWithPermissions(['sales', 'sales.orders']);

    post(route('admin.appearance.themes.preview', $this->previewCode))->assertUnauthorized();
});

// ============================================================================
// Rendering
// ============================================================================

it('should render the storefront in the previewed theme with the preview bar', function () {
    $this->loginAsAdmin();

    freshThemes();

    withSession([ThemePreview::SESSION_KEY => $this->previewCode])
        ->get(route('shop.home.index'))
        ->assertOk()
        ->assertSee('Preview Theme Home')
        ->assertSee(trans('shop::app.layouts.theme-preview.previewing', ['theme' => 'Preview Theme']))
        ->assertSee(route('admin.appearance.themes.stop_preview'), false);

    expect(core()->getDefaultChannel()->fresh()->theme)->not->toBe($this->previewCode);
});

it('should keep the live theme for a visitor who is not a signed in admin', function () {
    freshThemes();

    withSession([ThemePreview::SESSION_KEY => $this->previewCode])
        ->get(route('shop.home.index'))
        ->assertOk()
        ->assertDontSee('Preview Theme Home')
        ->assertDontSee(trans('shop::app.layouts.theme-preview.exit'));
});

it('should keep the live theme for an admin who may not preview', function () {
    $this->loginAsAdminWithPermissions(['sales', 'sales.orders']);

    freshThemes();

    withSession([ThemePreview::SESSION_KEY => $this->previewCode])
        ->get(route('shop.home.index'))
        ->assertOk()
        ->assertDontSee('Preview Theme Home');
});

it('should keep the live theme once the preview stops', function () {
    $this->loginAsAdmin();

    post(route('admin.appearance.themes.stop_preview'));

    freshThemes();

    get(route('shop.home.index'))
        ->assertOk()
        ->assertDontSee('Preview Theme Home');
});

// ============================================================================
// Caching
// ============================================================================

it('should keep the page cache off while a theme is previewed', function () {
    config(['responsecache.enabled' => true]);

    $this->loginAsAdmin();

    $profile = app(FullPageCacheProfile::class);

    expect($profile->enabled(request()))->toBeTrue();

    session()->put(ThemePreview::SESSION_KEY, $this->previewCode);

    expect($profile->enabled(request()))->toBeFalse();
});

// ============================================================================
// Gallery
// ============================================================================

it('should offer a preview of an installed theme in the gallery', function () {
    $this->loginAsAdmin();

    get(route('admin.appearance.themes.index'))
        ->assertOk()
        ->assertSee(route('admin.appearance.themes.preview', ['code' => '__CODE__']), false);
});
