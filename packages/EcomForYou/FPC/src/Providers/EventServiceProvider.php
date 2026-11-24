<?php

namespace EcomForYou\FPC\Providers;

use EcomForYou\FPC\Listeners\Category;
use EcomForYou\FPC\Listeners\Channel;
use EcomForYou\FPC\Listeners\CoreConfig;
use EcomForYou\FPC\Listeners\Order;
use EcomForYou\FPC\Listeners\Page;
use EcomForYou\FPC\Listeners\Price;
use EcomForYou\FPC\Listeners\Product;
use EcomForYou\FPC\Listeners\Refund;
use EcomForYou\FPC\Listeners\Review;
use EcomForYou\FPC\Listeners\Section;
use EcomForYou\FPC\Listeners\Theme;
use EcomForYou\FPC\Listeners\URLRewrite;
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
         * Catalog events.
         */
        'catalog.product.create.after' => [
            [Product::class, 'afterCreate'],
        ],

        'catalog.product.update.after' => [
            [Product::class, 'afterUpdate'],
        ],

        'catalog.product.delete.before' => [
            [Product::class, 'beforeDelete'],
        ],

        'catalog.category.create.after' => [
            [Category::class, 'afterCreate'],
        ],

        'catalog.category.update.after' => [
            [Category::class, 'afterUpdate'],
        ],

        'catalog.category.delete.before' => [
            [Category::class, 'beforeDelete'],
        ],

        'catalog.product.price.reindex.after' => [
            [Price::class, 'afterReindex'],
        ],

        /**
         * Promotion events.
         */
        'promotions.catalog_rule.reindex.after' => [
            [Price::class, 'afterReindex'],
        ],

        /**
         * Customer events.
         */
        'customer.review.update.after' => [
            [Review::class, 'afterUpdate'],
        ],

        'customer.review.delete.before' => [
            [Review::class, 'beforeDelete'],
        ],

        /**
         * Sales events.
         */
        'checkout.order.save.after' => [
            [Order::class, 'afterCancelOrCreate'],
        ],

        'sales.order.cancel.after' => [
            [Order::class, 'afterCancelOrCreate'],
        ],

        'sales.refund.save.after' => [
            [Refund::class, 'afterCreate'],
        ],

        /**
         * CMS events.
         */
        'cms.page.update.after' => [
            [Page::class, 'afterUpdate'],
        ],

        'cms.page.delete.before' => [
            [Page::class, 'beforeDelete'],
        ],

        /**
         * Theme events.
         */
        'section.create.after' => [
            [Section::class, 'afterCreate'],
        ],

        'section.update.after' => [
            [Section::class, 'afterUpdate'],
        ],

        'section.delete.before' => [
            [Section::class, 'beforeDelete'],
        ],

        'appearance.theme.upload.after' => [
            [Theme::class, 'afterUpdate'],
        ],

        'appearance.theme.rollback.after' => [
            [Theme::class, 'afterUpdate'],
        ],

        'appearance.theme.image.update.after' => [
            [Theme::class, 'afterUpdate'],
        ],

        /**
         * Core events.
         */
        'core.channel.update.after' => [
            [Channel::class, 'afterUpdate'],
        ],

        'core.configuration.save.after' => [
            [CoreConfig::class, 'afterUpdate'],
        ],

        /**
         * Marketing events.
         */
        'marketing.search_seo.url_rewrites.update.after' => [
            [URLRewrite::class, 'afterUpdate'],
        ],

        'marketing.search_seo.url_rewrites.delete.before' => [
            [URLRewrite::class, 'beforeDelete'],
        ],
    ];
}
