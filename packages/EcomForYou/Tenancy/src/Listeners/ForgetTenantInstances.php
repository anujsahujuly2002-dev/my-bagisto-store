<?php

namespace EcomForYou\Tenancy\Listeners;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Laravel\Octane\Events\RequestTerminated;

class ForgetTenantInstances
{
    /**
     * Create a new listener instance.
     */
    public function __construct(protected Application $app) {}

    /**
     * Forget every instance that holds one store's state, in the container and behind its facade, so the next
     * use builds it for whichever store is current. After an Octane request, the request's own sandbox is cleared.
     */
    public function handle(?object $event = null): void
    {
        $app = $event instanceof RequestTerminated ? $event->sandbox : $this->app;

        foreach (config('tenancy.instances', []) as $abstract) {
            $app->forgetInstance($abstract);

            Facade::clearResolvedInstance($abstract);
        }
    }
}
