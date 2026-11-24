<?php

namespace EcomForYou\Checkout\Providers;

use EcomForYou\Checkout\Http\Middleware\ResolveBuyNowCart;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        include __DIR__.'/../Http/helpers.php';
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('cart.buy_now', ResolveBuyNowCart::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(EventServiceProvider::class);
    }
}
