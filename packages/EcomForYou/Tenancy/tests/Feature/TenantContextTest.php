<?php

use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Tenancy\Exceptions\NoTenantSelectedException;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Octane\Events\RequestTerminated;

beforeEach(function () {
    Event::fake([TenantMadeCurrent::class, TenantForgotten::class]);
});

// ============================================================================
// Landlord Records
// ============================================================================

it('should give a new tenant a uuid and a database named after it', function () {
    $tenant = $this->createTenant(['subdomain' => '  My-Store  ']);

    expect($tenant->uuid)->toBeString()->toHaveLength(36)
        ->and($tenant->database)->toBe(config('tenancy.database.prefix').str_replace('-', '', $tenant->uuid))
        ->and($tenant->subdomain)->toBe('my-store')
        ->and($tenant->status)->toBe(TenantStatus::ACTIVE);
});

it('should start a tenant waiting for its email to be verified', function () {
    $tenant = app(TenantRepository::class)->create(['name' => 'New Store', 'subdomain' => 'new-store-1']);

    expect($tenant->fresh()->status)->toBe(TenantStatus::PENDING_VERIFICATION);
});

it('should find a tenant by its subdomain whatever the case', function () {
    $tenant = $this->createTenant(['subdomain' => 'find-me-store']);

    expect(app(TenantRepository::class)->findBySubdomain('FIND-ME-STORE')?->id)->toBe($tenant->id)
        ->and(app(TenantRepository::class)->findBySubdomain('nobody-here'))->toBeNull();
});

it('should build the tenant url from its subdomain and the platform domain', function () {
    config(['tenancy.platform_domain' => 'platform.test', 'tenancy.scheme' => 'https']);

    $tenant = $this->createTenant(['subdomain' => 'url-store']);

    expect($tenant->host())->toBe('url-store.platform.test')
        ->and($tenant->url())->toBe('https://url-store.platform.test');
});

it('should keep the landlord migrations out of every store migration run', function () {
    $landlordPath = realpath(dirname(__DIR__, 2).'/src/Database/Migrations/Landlord');

    $paths = array_map(fn (string $path) => realpath($path) ?: $path, app('migrator')->paths());

    expect($paths)->not->toContain($landlordPath);
});

it('should run the landlord migrations again without changing anything', function () {
    $this->artisan('landlord:migrate', ['--force' => true])->assertSuccessful();
});

// ============================================================================
// Tenant Context
// ============================================================================

it('should make a tenant current and announce it', function () {
    $tenant = $this->createTenant();

    $context = app(TenantContext::class);

    $context->makeCurrent($tenant);

    expect($context->check())->toBeTrue()
        ->and($context->current()->is($tenant))->toBeTrue();

    Event::assertDispatched(TenantMadeCurrent::class, fn ($event) => $event->tenant->is($tenant));

    $context->forget();
});

it('should forget the current tenant and announce it', function () {
    $tenant = $this->createTenant();

    $context = app(TenantContext::class);

    $context->makeCurrent($tenant);

    $context->forget();

    expect($context->check())->toBeFalse();

    Event::assertDispatched(TenantForgotten::class, fn ($event) => $event->tenant->is($tenant));
});

it('should announce nothing when forgetting with no tenant current', function () {
    app(TenantContext::class)->forget();

    Event::assertNotDispatched(TenantForgotten::class);
});

it('should run a callback inside a tenant and put the previous tenant back', function () {
    $first = $this->createTenant();

    $second = $this->createTenant();

    $context = app(TenantContext::class);

    $context->makeCurrent($first);

    $seen = $context->run($second, fn () => $context->current()->id);

    expect($seen)->toBe($second->id)
        ->and($context->current()->is($first))->toBeTrue();

    $context->forget();
});

it('should leave no tenant current after a callback that fails', function () {
    $tenant = $this->createTenant();

    $context = app(TenantContext::class);

    expect(fn () => $context->run($tenant, fn () => throw new RuntimeException('boom')))
        ->toThrow(RuntimeException::class, 'boom')
        ->and($context->check())->toBeFalse();
});

it('should forget the tenant when an octane request ends', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->createTenant());

    Event::dispatch(RequestTerminated::class);

    expect($context->check())->toBeFalse();
});

// ============================================================================
// Switching Tasks
// ============================================================================

it('should switch the connection, cache, disks, search prefix and url to the tenant', function () {
    config(['tenancy.platform_domain' => 'platform.test', 'tenancy.scheme' => 'https']);

    $tenant = $this->createTenant(['subdomain' => 'switch-store']);

    $originalPrefix = config('cache.prefix');

    app(TenantContext::class)->makeCurrent($tenant);

    expect(DB::getDefaultConnection())->toBe(config('tenancy.database.connection'))
        ->and(config('database.connections.tenant.database'))->toBe($tenant->database)
        ->and(config('cache.prefix'))->toBe($originalPrefix.'tenant_'.$tenant->id.':')
        ->and(config('elasticsearch.index_prefix'))->toBe('t'.$tenant->id.'_')
        ->and(config('app.url'))->toBe('https://switch-store.platform.test')
        ->and(config('filesystems.disks.public.root'))->toBe(storage_path('app/public/tenants/'.$tenant->id))
        ->and(config('filesystems.disks.private.root'))->toBe(storage_path('app/private/tenants/'.$tenant->id))
        ->and(Storage::disk('public')->url('product/1/a.webp'))
        ->toBe('https://switch-store.platform.test/storage/tenants/'.$tenant->id.'/product/1/a.webp');

    app(TenantContext::class)->forget();
});

it('should keep the search prefix a tenant record carries', function () {
    $tenant = $this->createTenant(['data' => ['index_prefix' => 'legacy_']]);

    app(TenantContext::class)->makeCurrent($tenant);

    expect(config('elasticsearch.index_prefix'))->toBe('legacy_');

    app(TenantContext::class)->forget();
});

it('should give the single store everything back when the tenant is forgotten with tenancy off', function () {
    $before = [
        'connection' => DB::getDefaultConnection(),
        'cache' => config('cache.prefix'),
        'search' => config('elasticsearch.index_prefix'),
        'url' => config('app.url'),
        'public' => config('filesystems.disks.public'),
        'private' => config('filesystems.disks.private'),
    ];

    app(TenantContext::class)->makeCurrent($this->createTenant());

    app(TenantContext::class)->forget();

    expect([
        'connection' => DB::getDefaultConnection(),
        'cache' => config('cache.prefix'),
        'search' => config('elasticsearch.index_prefix'),
        'url' => config('app.url'),
        'public' => config('filesystems.disks.public'),
        'private' => config('filesystems.disks.private'),
    ])->toBe($before);
});

// ============================================================================
// Fail Closed With Tenancy On
// ============================================================================

it('should refuse every store query while tenancy is on and no tenant is current', function () {
    config(['tenancy.enabled' => true]);

    app(TenantContext::class)->forget();

    expect(fn () => DB::table('channels')->count())->toThrow(QueryException::class, 'No tenant selected');
});

it('should refuse the store disks while tenancy is on and no tenant is current', function () {
    config(['tenancy.enabled' => true]);

    app(TenantContext::class)->forget();

    expect(fn () => Storage::disk('public')->put('leak.txt', 'x'))->toThrow(NoTenantSelectedException::class)
        ->and(fn () => Storage::disk('private')->exists('leak.txt'))->toThrow(NoTenantSelectedException::class);
});

it('should reach the tenant database once the tenant is current', function () {
    $tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);

    config(['tenancy.enabled' => true]);

    app(TenantContext::class)->forget();

    app(TenantContext::class)->makeCurrent($tenant);

    expect(DB::connection()->getName())->toBe(config('tenancy.database.connection'))
        ->and(DB::connection()->getDatabaseName())->toBe($tenant->database)
        ->and(DB::table('channels')->exists())->toBeTrue();

    app(TenantContext::class)->forget();
});

it('should let a connection held before identification follow the tenant once it is current', function () {
    $tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);

    config(['tenancy.enabled' => true]);

    app(TenantContext::class)->forget();

    $held = DB::connection();

    expect(fn () => $held->table('channels')->exists())->toThrow(QueryException::class, 'No tenant selected');

    app(TenantContext::class)->makeCurrent($tenant);

    expect($held)->toBe(DB::connection())
        ->and($held->getDatabaseName())->toBe($tenant->database)
        ->and($held->table('channels')->exists())->toBeTrue();

    app(TenantContext::class)->forget();

    expect(fn () => $held->table('channels')->exists())->toThrow(QueryException::class, 'No tenant selected');
});
