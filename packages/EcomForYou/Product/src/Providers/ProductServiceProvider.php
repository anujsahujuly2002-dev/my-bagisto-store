<?php

namespace EcomForYou\Product\Providers;

use EcomForYou\Product\Console\Commands\Indexer;
use EcomForYou\Product\Console\Commands\PruneSearchImages;
use EcomForYou\Product\Models\ProductProxy;
use EcomForYou\Product\Observers\ProductObserver;
use EcomForYou\Product\Services\Search\Connections\ElasticConnection;
use EcomForYou\Product\Services\Search\Engines\DatabaseEngine;
use EcomForYou\Product\Services\Search\Engines\ElasticSearchEngine;
use EcomForYou\Product\Services\Search\Indexers\ElasticSearchIndexer;
use EcomForYou\Product\Services\Search\SearchEngineConfigurator;
use EcomForYou\Product\Services\Search\SearchEngineManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        include __DIR__.'/../Http/helpers.php';

        $this->registerConfig();

        $this->registerCommands();

        $this->registerSearchBindings();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'product');

        app(SearchEngineConfigurator::class)->configure();

        ProductProxy::observe(ProductObserver::class);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('indexer:index --type=price')->dailyAt('00:01');

            $schedule->command('search-images:prune')->dailyAt('00:05');
        });

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register configuration.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/product_types.php', 'product_types');
    }

    /**
     * Register the console commands of this package.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Indexer::class, PruneSearchImages::class]);
        }
    }

    /**
     * Register search engine bindings.
     */
    protected function registerSearchBindings(): void
    {
        $this->app->singleton(SearchEngineManager::class);

        $this->app->singleton('product.search.connection.elastic', ElasticConnection::class);

        $this->app->singleton('product.search.engine.database', DatabaseEngine::class);

        $this->app->singleton('product.search.engine.elastic', ElasticSearchEngine::class);

        $this->app->singleton('product.search.indexer.elastic', ElasticSearchIndexer::class);
    }
}
