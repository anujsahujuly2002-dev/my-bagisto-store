<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Contracts\Foundation\Application;

class CacheTask implements SwitchingTask
{
    /**
     * The cache prefix the single store used before any switch.
     */
    protected string $originalPrefix;

    /**
     * Create the task, remembering the single store's cache prefix.
     */
    public function __construct(protected Application $app)
    {
        $this->originalPrefix = (string) config('cache.prefix');
    }

    /**
     * Prefix every cache key with `tenant_{id}:`.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->usePrefix($this->originalPrefix.'tenant_'.$tenant->id.':');
    }

    /**
     * Put the single store's cache prefix back.
     */
    public function forgetCurrent(): void
    {
        $this->usePrefix($this->originalPrefix);
    }

    /**
     * Set the prefix and drop every resolved store, so the next use builds one with it.
     */
    protected function usePrefix(string $prefix): void
    {
        config(['cache.prefix' => $prefix]);

        $this->app['cache']->forgetDriver(array_keys(config('cache.stores', [])));

        $this->app->forgetInstance('cache.store');
    }
}
