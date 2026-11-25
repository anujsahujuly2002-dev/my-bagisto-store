<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\ResponseCache;

class ResponseCacheTask implements SwitchingTask
{
    /**
     * The cache store the page cache is given while a tenant is current, built from the configured one.
     */
    public const TENANT_STORE = 'responsecache_tenant';

    /**
     * The drivers whose stores support tags, so clearing a tag flushes one store's pages instead of the whole store.
     */
    public const TAGGABLE_DRIVERS = ['array', 'memcached', 'redis'];

    /**
     * The page cache settings the single store used before any switch.
     *
     * @var array<string, mixed>
     */
    protected array $original;

    /**
     * Create the task, remembering the single store's page cache settings.
     */
    public function __construct(protected Application $app)
    {
        $this->original = (array) config('responsecache.cache');
    }

    /**
     * Give the page cache a home only the tenant's pages live in, so clearing it, which flushes the whole cache
     * store, never reaches another store's pages: its own folder on a file store, its own database on a database
     * store, and the `tenant_{id}` tag on a store that supports tags.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->restore();

        $name = $this->original['store'] ?? config('cache.default');

        $store = (array) config("cache.stores.{$name}");

        match ($store['driver'] ?? null) {
            'file' => $this->useStore(array_merge($store, [
                'path' => rtrim((string) $store['path'], '/').'/tenants/'.$tenant->getKey(),
                'lock_path' => rtrim((string) ($store['lock_path'] ?? $store['path']), '/').'/tenants/'.$tenant->getKey(),
            ])),
            'database' => $this->useStore(array_merge($store, [
                'connection' => null,
                'lock_connection' => null,
            ])),
            default => in_array($store['driver'] ?? null, self::TAGGABLE_DRIVERS, true)
                ? config(['responsecache.cache.tag' => 'tenant_'.$tenant->getKey()])
                : null,
        };

        $this->forgetResolved();
    }

    /**
     * Put the single store's page cache settings back.
     */
    public function forgetCurrent(): void
    {
        $this->restore();

        $this->forgetResolved();
    }

    /**
     * Register the tenant's page cache store and point the page cache at it.
     *
     * @param  array<string, mixed>  $store
     */
    protected function useStore(array $store): void
    {
        config([
            'cache.stores.'.self::TENANT_STORE => $store,
            'responsecache.cache.store' => self::TENANT_STORE,
        ]);
    }

    /**
     * Put back the page cache settings and drop the tenant's store, so no tenant's home outlives it.
     */
    protected function restore(): void
    {
        config(['responsecache.cache' => $this->original]);

        config(['cache.stores.'.self::TENANT_STORE => null]);

        $this->app['cache']->forgetDriver(self::TENANT_STORE);
    }

    /**
     * Drop the page cache services that hold a cache store, so the next use builds them against the new one.
     */
    protected function forgetResolved(): void
    {
        $this->app->forgetInstance('responsecache');

        $this->app->forgetInstance(ResponseCache::class);

        $this->app->forgetInstance(CacheResponse::class);

        Facade::clearResolvedInstance('responsecache');
    }
}
