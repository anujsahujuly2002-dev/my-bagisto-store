<?php

namespace EcomForYou\Shop\Providers;

use EcomForYou\EUWithdrawal\Events\WithdrawalReceived;
use EcomForYou\Shop\Listeners\CatalogCache;
use EcomForYou\Shop\Listeners\Customer;
use EcomForYou\Shop\Listeners\EUWithdrawal\SendConfirmation as EUWithdrawalSendConfirmation;
use EcomForYou\Shop\Listeners\GDPR;
use EcomForYou\Shop\Listeners\Invoice;
use EcomForYou\Shop\Listeners\Order;
use EcomForYou\Shop\Listeners\Refund;
use EcomForYou\Shop\Listeners\Shipment;
use EcomForYou\Shop\Listeners\ThemePreviewBar;
use EcomForYou\Shop\Listeners\UploadedTheme;
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
         * EU Withdrawal — durable-medium confirmation email
         * (Directive (EU) 2023/2673, Art. 11a(3)). Sent synchronously by the
         * listener so a queue failure cannot leave a withdrawal unconfirmed.
         */
        WithdrawalReceived::class => [
            [EUWithdrawalSendConfirmation::class, 'handle'],
        ],

        /**
         * Catalog cache invalidation. Any change that can alter a cached
         * storefront API response bumps the catalog version so listings are
         * served fresh: product/category saves, review status changes or
         * deletions (ratings and review counts), and order/cancel/refund
         * (stock-driven saleability). The order and refund events are wired
         * in the "Sales" section below, next to their existing listeners.
         */
        'catalog.product.create.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.product.update.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.product.delete.before' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.category.create.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.category.update.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.category.delete.before' => [
            [CatalogCache::class, 'flush'],
        ],

        'catalog.product.price.reindex.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'promotions.catalog_rule.reindex.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'customer.review.update.after' => [
            [CatalogCache::class, 'flush'],
        ],

        'customer.review.delete.before' => [
            [CatalogCache::class, 'flush'],
        ],

        /**
         * Customer related events.
         */
        'customer.registration.after' => [
            [Customer::class, 'afterCreated'],
        ],

        'customer.password.update.after' => [
            [Customer::class, 'afterPasswordUpdated'],
        ],

        'customer.subscription.after' => [
            [Customer::class, 'afterSubscribed'],
        ],

        'customer.note.create.after' => [
            [Customer::class, 'afterNoteCreated'],
        ],

        /**
         * GDPR related events.
         */
        'customer.account.gdpr-request.create.after' => [
            [GDPR::class, 'afterGdprRequestCreated'],
        ],

        'customer.account.gdpr-request.update.after' => [
            [GDPR::class, 'afterGdprRequestUpdated'],
        ],

        /**
         * Sales related events.
         */
        'checkout.order.save.after' => [
            [Order::class, 'afterCreated'],
            [CatalogCache::class, 'flush'],
        ],

        'sales.order.cancel.after' => [
            [Order::class, 'afterCanceled'],
            [CatalogCache::class, 'flush'],
        ],

        'sales.order.comment.create.after' => [
            [Order::class, 'afterCommented'],
        ],

        'sales.invoice.save.after' => [
            [Invoice::class, 'afterCreated'],
        ],

        'sales.invoice.send_duplicate_email' => [
            [Invoice::class, 'afterCreated'],
        ],

        'sales.shipment.save.after' => [
            [Shipment::class, 'afterCreated'],
        ],

        'sales.refund.save.after' => [
            [Refund::class, 'afterCreated'],
            [CatalogCache::class, 'flush'],
        ],

        /**
         * Uploaded theme events, adding a template's own styles and scripts to the layout.
         */
        'bagisto.shop.layout.head.after' => [
            [UploadedTheme::class, 'addStyles'],
        ],

        'bagisto.shop.layout.vue-app-mount.after' => [
            [UploadedTheme::class, 'addScripts'],
        ],

        /**
         * Theme preview events, marking every page an admin previews a theme on.
         */
        'bagisto.shop.layout.body.before' => [
            [ThemePreviewBar::class, 'add'],
        ],
    ];
}
