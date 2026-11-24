<?php

namespace EcomForYou\Omnibus\Providers;

use EcomForYou\Omnibus\Listeners\ProductPriceChange;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'catalog.product.create.after' => [
            ProductPriceChange::class,
        ],

        'catalog.product.update.after' => [
            ProductPriceChange::class,
        ],
    ];
}
