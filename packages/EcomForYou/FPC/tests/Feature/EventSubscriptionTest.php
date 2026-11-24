<?php

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
use EcomForYou\FPC\Listeners\URLRewrite;
use Illuminate\Support\Facades\Event;

// ============================================================================
// Listener Registration
// ============================================================================

it('should subscribe the page cache to the event', function (string $event, string $listener, string $method) {
    $registered = Event::getRawListeners()[$event] ?? [];

    expect($registered)->toContain([$listener, $method]);
})->with([
    'product created' => ['catalog.product.create.after', Product::class, 'afterCreate'],
    'product updated' => ['catalog.product.update.after', Product::class, 'afterUpdate'],
    'product deleted' => ['catalog.product.delete.before', Product::class, 'beforeDelete'],
    'category created' => ['catalog.category.create.after', Category::class, 'afterCreate'],
    'category updated' => ['catalog.category.update.after', Category::class, 'afterUpdate'],
    'category deleted' => ['catalog.category.delete.before', Category::class, 'beforeDelete'],
    'product prices reindexed' => ['catalog.product.price.reindex.after', Price::class, 'afterReindex'],
    'catalog rule prices reindexed' => ['promotions.catalog_rule.reindex.after', Price::class, 'afterReindex'],
    'review updated' => ['customer.review.update.after', Review::class, 'afterUpdate'],
    'review deleted' => ['customer.review.delete.before', Review::class, 'beforeDelete'],
    'order placed' => ['checkout.order.save.after', Order::class, 'afterCancelOrCreate'],
    'order cancelled' => ['sales.order.cancel.after', Order::class, 'afterCancelOrCreate'],
    'refund saved' => ['sales.refund.save.after', Refund::class, 'afterCreate'],
    'page updated' => ['cms.page.update.after', Page::class, 'afterUpdate'],
    'page deleted' => ['cms.page.delete.before', Page::class, 'beforeDelete'],
    'section created' => ['section.create.after', Section::class, 'afterCreate'],
    'section updated' => ['section.update.after', Section::class, 'afterUpdate'],
    'section deleted' => ['section.delete.before', Section::class, 'beforeDelete'],
    'channel updated' => ['core.channel.update.after', Channel::class, 'afterUpdate'],
    'configuration saved' => ['core.configuration.save.after', CoreConfig::class, 'afterUpdate'],
    'url rewrite updated' => ['marketing.search_seo.url_rewrites.update.after', URLRewrite::class, 'afterUpdate'],
    'url rewrite deleted' => ['marketing.search_seo.url_rewrites.delete.before', URLRewrite::class, 'beforeDelete'],
]);
