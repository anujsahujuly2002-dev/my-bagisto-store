<?php

namespace EcomForYou\Tenancy\Providers;

use EcomForYou\Tenancy\Console\Commands\CreateTenant;
use EcomForYou\Tenancy\Console\Commands\DeleteTenant;
use EcomForYou\Tenancy\Console\Commands\LandlordMigrate;
use EcomForYou\Tenancy\Console\Commands\MigrateTenants;
use EcomForYou\Tenancy\Console\Commands\RunForTenants;
use EcomForYou\Tenancy\Console\Commands\RunTenantSchedules;
use EcomForYou\Tenancy\Console\Commands\SeedTenants;
use EcomForYou\Tenancy\Console\Commands\SuspendTenant;
use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Tenancy\Exceptions\NoTenantSelectedException;
use EcomForYou\Tenancy\Listeners\ForgetTenantInstances;
use EcomForYou\Tenancy\Listeners\TenantAwareJobs;
use EcomForYou\Tenancy\Support\ConfigurationGuard;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestTerminated;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register the tenant context, and the drivers that refuse store access while no tenant is current.
     */
    public function register(): void
    {
        $this->pinTemplateConnection();

        $this->app->singleton(TenantContext::class);

        $this->app->singleton(TenantAwareJobs::class);

        $this->callAfterResolving('db', fn (DatabaseManager $db) => $this->guardTenantConnection($db));

        $this->callAfterResolving('filesystem', fn (FilesystemManager $filesystem) => $filesystem->extend(
            FilesystemTask::UNAVAILABLE_DRIVER,
            fn () => throw new NoTenantSelectedException('files')
        ));

        $this->app->booting(fn () => $this->lockDown());
    }

    /**
     * Bootstrap the translations, views, commands, the per-request reset and the per-job tenant.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'tenancy');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'tenancy');

        if ($this->app->runningInConsole()) {
            $this->commands([
                LandlordMigrate::class,
                CreateTenant::class,
                MigrateTenants::class,
                RunForTenants::class,
                SeedTenants::class,
                DeleteTenant::class,
                SuspendTenant::class,
                RunTenantSchedules::class,
            ]);
        }

        Event::listen([TenantMadeCurrent::class, TenantForgotten::class], ForgetTenantInstances::class);

        Event::listen(RequestTerminated::class, fn () => $this->app->make(TenantContext::class)->forget());

        Event::listen(RequestTerminated::class, ForgetTenantInstances::class);

        $this->makeJobsTenantAware();
    }

    /**
     * Stamp every job with the tenant it was dispatched for, and run it with that tenant current.
     */
    protected function makeJobsTenantAware(): void
    {
        Queue::createPayloadUsing(fn () => $this->app->make(TenantAwareJobs::class)->payload());

        Event::listen(JobProcessing::class, [TenantAwareJobs::class, 'processing']);

        Event::listen([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class], [TenantAwareJobs::class, 'finished']);
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

        $this->keepQueueTablesInLandlord();

        $this->app->make(TenantContext::class)->forget();
    }

    /**
     * Keep the database queue, job batches and failed jobs on the landlord connection, so one worker serves every
     * store and no job waits in a database that is only reachable once its tenant is current.
     */
    protected function keepQueueTablesInLandlord(): void
    {
        $landlord = config('tenancy.landlord_connection');

        config([
            'queue.connections.database.connection' => $landlord,
            'queue.batching.database' => $landlord,
            'queue.failed.database' => $landlord,
        ]);
    }

    /**
     * Name the template connection while the default is still the single store's, since the tenant connection takes
     * the default over once tenancy is on and every later reader needs the template it was built from.
     */
    protected function pinTemplateConnection(): void
    {
        if (blank(config('tenancy.database.template'))) {
            config(['tenancy.database.template' => config('database.default')]);
        }
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
