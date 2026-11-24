<?php

namespace EcomForYou\CatalogRule\Providers;

use EcomForYou\CatalogRule\Listeners\CatalogRule;
use EcomForYou\CatalogRule\Listeners\Product;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /**
         * Catalog rule events.
         */
        'promotions.catalog_rule.create.after' => [
            [CatalogRule::class, 'afterUpdateCreate'],
        ],

        'promotions.catalog_rule.update.after' => [
            [CatalogRule::class, 'afterUpdateCreate'],
        ],

        'promotions.catalog_rule.update.before' => [
            [CatalogRule::class, 'beforeUpdate'],
        ],

        'promotions.catalog_rule.delete.before' => [
            [CatalogRule::class, 'beforeDelete'],
        ],

        /**
         * Product events.
         */
        'catalog.product.update.after' => [
            [Product::class, 'afterUpdate'],
        ],
    ];
}
