<?php

namespace EcomForYou\Product\Providers;

use EcomForYou\Product\Listeners\AttributeFamily;
use EcomForYou\Product\Listeners\Category;
use EcomForYou\Product\Listeners\Import;
use EcomForYou\Product\Listeners\InventorySource;
use EcomForYou\Product\Listeners\Order;
use EcomForYou\Product\Listeners\Product;
use EcomForYou\Product\Listeners\Refund;
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
            [Product::class, 'afterCreate'],
        ],

        'catalog.product.update.after' => [
            [Product::class, 'afterUpdate'],
        ],

        'catalog.product.delete.before' => [
            [Product::class, 'beforeDelete'],
        ],

        'catalog.category.update.after' => [
            [Category::class, 'afterUpdate'],
        ],

        'catalog.category.delete.after' => [
            [Category::class, 'afterDelete'],
        ],

        'catalog.attribute_family.update.after' => [
            [AttributeFamily::class, 'afterUpdate'],
        ],

        'data_transfer.imports.batch.indexing.after' => [
            [Import::class, 'afterBatchIndexing'],
        ],

        'inventory.inventory_source.delete.after' => [
            [InventorySource::class, 'afterDelete'],
        ],

        'checkout.order.save.after' => [
            [Order::class, 'afterCancelOrCreate'],
        ],

        'sales.order.cancel.after' => [
            [Order::class, 'afterCancelOrCreate'],
        ],

        'sales.refund.save.after' => [
            [Refund::class, 'afterCreate'],
        ],
    ];
}
