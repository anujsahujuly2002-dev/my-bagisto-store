<?php

namespace EcomForYou\Tenancy\Providers;

use EcomForYou\Tenancy\Console\Commands\LandlordMigrate;
use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Tenancy\Exceptions\NoTenantSelectedException;
use EcomForYou\Tenancy\Listeners\ForgetTenantInstances;
use EcomForYou\Tenancy\Support\ConfigurationGuard;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestTerminated;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register the tenant context, and the drivers that refuse store access while no tenant is current.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        $this->callAfterResolving('db', fn (DatabaseManager $db) => $this->guardTenantConnection($db));

        $this->callAfterResolving('filesystem', fn (FilesystemManager $filesystem) => $filesystem->extend(
            FilesystemTask::UNAVAILABLE_DRIVER,
            fn () => throw new NoTenantSelectedException('files')
        ));

        $this->app->booting(fn () => $this->lockDown());
    }

    /**
     * Bootstrap the translations, views, commands and the per-request reset.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'tenancy');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'tenancy');

        if ($this->app->runningInConsole()) {
            $this->commands([
                LandlordMigrate::class,
            ]);
        }

        Event::listen([TenantMadeCurrent::class, TenantForgotten::class], ForgetTenantInstances::class);

        Event::listen(RequestTerminated::class, fn () => $this->app->make(TenantContext::class)->forget());

        Event::listen(RequestTerminated::class, ForgetTenantInstances::class);
    }

    /**
     * With tenancy on, check the configuration and leave no tenant current before any provider boots.
     */
    protected function lockDown(): void
    {
        if (! config('tenancy.enabled')) {
            return;
        }

        $this->app->make(ConfigurationGuard::class)->check();

        $this->app->make(TenantContext::class)->forget();
    }

    /**
     * Build the tenant connection so it can be held before identification, but refuses to connect while it has no
     * database: a store query before a tenant is current fails instead of reaching any store.
     */
    protected function guardTenantConnection(DatabaseManager $db): void
    {
        $db->extend(config('tenancy.database.connection'), function (array $config, string $name) {
            $connection = $this->app['db.factory']->make($config, $name);

            if (filled($config['database'] ?? null)) {
                return $connection;
            }

            $refuse = fn () => throw new NoTenantSelectedException;

            return $connection->setPdo($refuse)->setReadPdo($refuse)->setDirectPdo($refuse);
        });
    }
}
