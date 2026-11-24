<?php

use EcomForYou\Core\Core;
use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Tenancy\Exceptions\NoTenantSelectedException;
use EcomForYou\Tenancy\TenantContext;
use EcomForYou\Theme\AdminThemeResolver;
use EcomForYou\Theme\Models\Theme;
use EcomForYou\Theme\ThemeRegistry;
use EcomForYou\Theme\Themes;
use EcomForYou\Theme\Upload\ThemeInstaller;
use EcomForYou\Theme\Upload\ThemeUploads;
use EcomForYou\Theme\Upload\UploadedPage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Octane\Events\RequestTerminated;

afterEach(function () {
    config(['tenancy.enabled' => false]);

    app(TenantContext::class)->forget();
});

// ============================================================================
// Per-Store Instances
// ============================================================================

it('should hand each store its own core, themes and theme helpers', function () {
    $stores = [$this->createTenant(), $this->createTenant()];

    Event::dispatch(new TenantMadeCurrent($stores[0]));

    $first = [core(), themes(), app(UploadedPage::class), app(AdminThemeResolver::class)];

    Event::dispatch(new TenantMadeCurrent($stores[1]));

    $second = [core(), themes(), app(UploadedPage::class), app(AdminThemeResolver::class)];

    expect($second[0])->toBeInstanceOf(Core::class)->not->toBe($first[0])
        ->and($second[1])->toBeInstanceOf(Themes::class)->not->toBe($first[1])
        ->and($second[2])->not->toBe($first[2])
        ->and($second[3])->not->toBe($first[3]);
});

it('should forget the store instances once no tenant is current', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->createTenant(['database' => DB::connection()->getDatabaseName()]));

    $core = core();

    $context->forget();

    expect(core())->not->toBe($core);
});

it('should forget the store instances after an Octane request even with no tenant current', function () {
    $core = core();

    Event::dispatch(new RequestTerminated(app(), app(), Request::create('/'), new Response));

    expect(core())->not->toBe($core);
});

it('should leave no tenant current when a store cannot be read once made current', function () {
    $context = app(TenantContext::class);

    expect(fn () => $context->makeCurrent($this->createTenant()))->toThrow(QueryException::class)
        ->and($context->check())->toBeFalse()
        ->and(config('database.default'))->not->toBe(config('tenancy.database.connection'));
});

// ============================================================================
// Themes
// ============================================================================

it('should register the store themes once its tenant is current and drop them once it is forgotten', function () {
    $tenant = $this->createTenant();

    Cache::forever(ThemeRegistry::CACHE_KEY, [Theme::PANEL_SHOP => ['store-only' => ['name' => 'Store Only']]]);

    Event::dispatch(new TenantMadeCurrent($tenant));

    expect(config('themes.shop.store-only.name'))->toBe('Store Only');

    Event::dispatch(new TenantForgotten($tenant));

    expect(config('themes.shop.store-only'))->toBeNull()
        ->and(config('themes.shop.default'))->not->toBeNull();
});

it('should forget the views located for the store before', function () {
    $tenant = $this->createTenant();

    view()->exists('shop::home.index');

    expect(app('view.finder')->getViews())->not->toBeEmpty();

    Event::dispatch(new TenantMadeCurrent($tenant));

    expect(app('view.finder')->getViews())->toBeEmpty();
});

// ============================================================================
// Local Files
// ============================================================================

it('should keep the single store local files under storage app while tenancy is off', function () {
    expect(tenant_storage_path())->toBe(storage_path('app'))
        ->and(tenant_storage_path('themes/demo'))->toBe(storage_path('app').DIRECTORY_SEPARATOR.'themes/demo');
});

it('should root a store local files in its own folder', function () {
    $tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);

    app(TenantContext::class)->makeCurrent($tenant);

    $root = storage_path("app/tenants/{$tenant->id}");

    expect(tenant_storage_path())->toBe($root)
        ->and(app(ThemeInstaller::class)->directory('demo'))->toBe($root.DIRECTORY_SEPARATOR.'themes/demo')
        ->and(app(ThemeUploads::class)->directory())->toBe($root.DIRECTORY_SEPARATOR.'theme-uploads/pending');
});

it('should resolve an uploaded theme views under the current store folder', function () {
    $tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);

    app(TenantContext::class)->makeCurrent($tenant);

    $theme = new Theme(['views_path' => 'themes/demo/releases/1/views', 'source' => Theme::SOURCE_UPLOAD]);

    expect($theme->viewsPathFromBase())->toBe("storage/app/tenants/{$tenant->id}/themes/demo/releases/1/views");
});

it('should refuse a store local path while tenancy is on and no tenant is current', function () {
    config(['tenancy.enabled' => true]);

    app(TenantContext::class)->forget();

    tenant_storage_path('themes/demo');
})->throws(NoTenantSelectedException::class);

it('should give the single store its local root back once tenancy is off', function () {
    app(TenantContext::class)->makeCurrent($this->createTenant(['database' => DB::connection()->getDatabaseName()]));

    app(TenantContext::class)->forget();

    expect(tenant_storage_path())->toBe(storage_path('app'));
});

// ============================================================================
// Installer
// ============================================================================

it('should close the web installer on every host while tenancy is on', function (string $url) {
    config([
        'tenancy.enabled' => true,
        'tenancy.platform_domain' => 'platform.test',
        'tenancy.central_hosts' => ['platform.test'],
    ]);

    $this->createTenant(['subdomain' => 'store-install', 'database' => DB::connection()->getDatabaseName()]);

    $this->get($url)->assertNotFound();
})->with([
    'central host' => 'http://platform.test/install',
    'store host' => 'http://store-install.platform.test/install',
]);
