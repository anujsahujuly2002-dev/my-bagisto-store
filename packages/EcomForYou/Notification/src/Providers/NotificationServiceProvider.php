<?php

namespace EcomForYou\Notification\Providers;

use EcomForYou\Notification\Notifier;
use EcomForYou\Notification\TypeRegistry;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        include __DIR__.'/../Http/helpers.php';

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/notification-groups.php', 'notification_groups');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/notification-types.php', 'notification_types');

        $this->app->singleton(TypeRegistry::class);

        $this->app->singleton(Notifier::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->booted(fn () => $this->app->make(Notifier::class)->listen());
    }
}
