<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Webkul\Core\Models\CoreConfig;
use Webkul\Theme\AdminThemeResolver;
use Webkul\Theme\Themes;
use Webkul\User\Models\Admin;

use function Pest\Laravel\get;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->adminThemeCode = 'admin-'.Str::lower(Str::random(10));

    $this->adminThemeViews = 'storage/framework/testing/'.$this->adminThemeCode.'/views';

    File::ensureDirectoryExists(base_path($this->adminThemeViews.'/account'));

    File::put(base_path($this->adminThemeViews.'/account/edit.blade.php'), "<p>Custom Admin Theme Account</p>\n");

    config(['themes.admin.'.$this->adminThemeCode => [
        'name' => 'Custom Admin',
        'parent' => 'default',
        'assets_path' => 'public/themes/admin/default',
        'views_path' => $this->adminThemeViews,
    ]]);

    app()->forgetInstance(Themes::class);

    Facade::clearResolvedInstance(Themes::class);
});

afterEach(function () {
    File::deleteDirectory(base_path('storage/framework/testing/'.$this->adminThemeCode));
});

/**
 * Store the store-wide admin theme the way Configuration saves it.
 */
function storeAdminTheme(string $code): void
{
    CoreConfig::query()->create([
        'code' => AdminThemeResolver::CONFIG_KEY,
        'value' => $code,
    ]);

    app(AdminThemeResolver::class)->forget();
}

/**
 * Resolve the admin theme afresh for the signed in admin.
 */
function resolvedAdminTheme(): string
{
    $resolver = app(AdminThemeResolver::class);

    $resolver->forget();

    return $resolver->resolve();
}

// ============================================================================
// Resolving
// ============================================================================

it('should use the default admin theme when nothing is chosen', function () {
    $this->loginAsAdmin();

    expect(resolvedAdminTheme())->toBe(config('themes.admin-default'));
});

it('should use the store-wide admin theme set in configuration', function () {
    $this->loginAsAdmin();

    storeAdminTheme($this->adminThemeCode);

    expect(resolvedAdminTheme())->toBe($this->adminThemeCode);
});

it('should prefer the admin\'s own theme over the store-wide one', function () {
    $other = 'admin-'.Str::lower(Str::random(10));

    File::ensureDirectoryExists(base_path('storage/framework/testing/'.$other.'/views'));

    config(['themes.admin.'.$other => [
        'name' => 'Other Admin',
        'parent' => 'default',
        'views_path' => 'storage/framework/testing/'.$other.'/views',
    ]]);

    $this->loginAsAdmin(Admin::factory()->create(['theme' => $this->adminThemeCode]));

    storeAdminTheme($other);

    expect(resolvedAdminTheme())->toBe($this->adminThemeCode);

    File::deleteDirectory(base_path('storage/framework/testing/'.$other));
});

it('should fall back past a chosen theme whose views are missing', function () {
    $this->loginAsAdmin(Admin::factory()->create(['theme' => $this->adminThemeCode]));

    File::deleteDirectory(base_path($this->adminThemeViews));

    expect(resolvedAdminTheme())->toBe(config('themes.admin-default'));
});

it('should fall back past a chosen theme whose build is missing', function () {
    config(['themes.admin.'.$this->adminThemeCode.'.vite' => [
        'hot_file' => 'missing-'.$this->adminThemeCode.'.hot',
        'build_directory' => 'themes/admin/'.$this->adminThemeCode.'/build',
        'package_assets_directory' => 'src/Resources/assets',
    ]]);

    $this->loginAsAdmin(Admin::factory()->create(['theme' => $this->adminThemeCode]));

    expect(resolvedAdminTheme())->toBe(config('themes.admin-default'));
});

it('should fall back past a theme that is no longer configured', function () {
    $this->loginAsAdmin(Admin::factory()->create(['theme' => 'removed-theme']));

    storeAdminTheme('another-removed-theme');

    expect(resolvedAdminTheme())->toBe(config('themes.admin-default'));
});

// ============================================================================
// Rendering
// ============================================================================

it('should render the admin panel with the admin\'s own theme', function () {
    $this->loginAsAdmin(Admin::factory()->create(['theme' => $this->adminThemeCode]));

    get(route('admin.account.edit'))
        ->assertOk()
        ->assertSee('Custom Admin Theme Account');
});

it('should render the admin panel with the default theme for an admin without a choice', function () {
    $this->loginAsAdmin();

    get(route('admin.account.edit'))
        ->assertOk()
        ->assertDontSee('Custom Admin Theme Account');
});

// ============================================================================
// Choosing
// ============================================================================

it('should offer every admin theme on the account page', function () {
    File::deleteDirectory(base_path($this->adminThemeViews));

    $this->loginAsAdmin();

    get(route('admin.account.edit'))
        ->assertOk()
        ->assertSee(trans('admin::app.account.edit.theme'))
        ->assertSee('Custom Admin');
});

it('should save the admin\'s own theme from the account page', function () {
    $admin = $this->loginAsAdmin();

    put(route('admin.account.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'current_password' => 'password',
        'theme' => $this->adminThemeCode,
    ])->assertRedirect();

    expect($admin->fresh()->theme)->toBe($this->adminThemeCode);

    put(route('admin.account.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'current_password' => 'password',
        'theme' => '',
    ])->assertRedirect();

    expect($admin->fresh()->theme)->toBeNull();
});

it('should refuse a theme that is not an admin theme', function () {
    $admin = $this->loginAsAdmin();

    put(route('admin.account.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'current_password' => 'password',
        'theme' => 'not-a-theme',
    ])->assertSessionHasErrors('theme');

    expect($admin->fresh()->theme)->toBeNull();
});

it('should list every admin theme as an option of the store-wide setting', function () {
    expect(app(AdminThemeResolver::class)->getConfigOptions())->toContain([
        'title' => 'Custom Admin',
        'value' => $this->adminThemeCode,
    ]);
});
