<?php

use EcomForYou\Tenancy\Tasks\ResponseCacheTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function () {
    $this->cachePath = storage_path('framework/testing/fpc-'.Str::lower(Str::random(10)));

    $this->tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);
});

afterEach(function () {
    app(TenantContext::class)->forget();

    File::deleteDirectory($this->cachePath);
});

/**
 * Point the page cache at a cache store, and rebuild the tenant context so it remembers that as the single store's.
 *
 * @param  array<string, mixed>  $store
 */
function usePageCacheStore(string $name, array $store): void
{
    config([
        "cache.stores.{$name}" => $store,
        'responsecache.cache.store' => $name,
        'responsecache.cache.tag' => '',
    ]);

    app()->forgetInstance(TenantContext::class);
}

// ============================================================================
// Per-Store Page Cache
// ============================================================================

it('should keep a store pages in its own folder on a file store and clear only that folder', function () {
    usePageCacheStore('fpc-file', ['driver' => 'file', 'path' => $this->cachePath]);

    $otherStorePage = $this->cachePath.'/tenants/999999/ab/cd/page';

    File::ensureDirectoryExists(dirname($otherStorePage));

    File::put($otherStorePage, 'another store page');

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(config('responsecache.cache.store'))->toBe(ResponseCacheTask::TENANT_STORE)
        ->and(config('cache.stores.'.ResponseCacheTask::TENANT_STORE.'.path'))->toBe($this->cachePath.'/tenants/'.$this->tenant->id);

    cache()->store(ResponseCacheTask::TENANT_STORE)->put('page', 'own page');

    ResponseCache::clear();

    expect(cache()->store(ResponseCacheTask::TENANT_STORE)->get('page'))->toBeNull()
        ->and(File::exists($otherStorePage))->toBeTrue();
});

it('should keep a store pages in its own database on a database store', function () {
    usePageCacheStore('fpc-database', ['driver' => 'database', 'connection' => 'mysql', 'table' => 'cache', 'lock_connection' => 'mysql']);

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(config('cache.stores.'.ResponseCacheTask::TENANT_STORE))
        ->toMatchArray(['driver' => 'database', 'connection' => null, 'lock_connection' => null, 'table' => 'cache']);
});

it('should tag a store pages with its own tag on a store that supports tags', function () {
    usePageCacheStore('fpc-array', ['driver' => 'array']);

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(config('responsecache.cache.tag'))->toBe('tenant_'.$this->tenant->id)
        ->and(config('responsecache.cache.store'))->toBe('fpc-array');
});

it('should give the single store its page cache settings back once no store is current', function () {
    usePageCacheStore('fpc-file', ['driver' => 'file', 'path' => $this->cachePath]);

    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    $context->forget();

    expect(config('responsecache.cache.store'))->toBe('fpc-file')
        ->and(config('responsecache.cache.tag'))->toBe('')
        ->and(config('cache.stores.'.ResponseCacheTask::TENANT_STORE))->toBeNull();
});
