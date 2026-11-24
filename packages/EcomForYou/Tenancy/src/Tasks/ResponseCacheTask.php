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
     * Create the task.
     */
    public function __construct(protected Application $app) {}

    /**
     * Make the page cache resolve its store again, so it picks up the tenant's cache prefix.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->forgetResolved();
    }

    /**
     * Make the page cache resolve its store again, so it drops the tenant's cache prefix.
     */
    public function forgetCurrent(): void
    {
        $this->forgetResolved();
    }

    /**
     * Drop the page cache services that hold a cache store.
     */
    protected function forgetResolved(): void
    {
        $this->app->forgetInstance('responsecache');

        $this->app->forgetInstance(ResponseCache::class);

        $this->app->forgetInstance(CacheResponse::class);

        Facade::clearResolvedInstance('responsecache');
    }
}
