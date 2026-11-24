<?php

namespace EcomForYou\Category\Providers;

use EcomForYou\Category\Models\CategoryProxy;
use EcomForYou\Category\Observers\CategoryObserver;
use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        CategoryProxy::observe(CategoryObserver::class);
    }
}
