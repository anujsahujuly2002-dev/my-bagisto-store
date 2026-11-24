<?php

use EcomForYou\FPC\Listeners\Order as OrderListener;
use EcomForYou\FPC\Listeners\Refund as RefundListener;
use EcomForYou\FPC\Listeners\Review as ReviewListener;
use EcomForYou\FPC\Listeners\URLRewrite as URLRewriteListener;
use EcomForYou\Marketing\Models\URLRewrite;
use EcomForYou\Product\Models\ProductReview;
use EcomForYou\Sales\Models\Order;
use EcomForYou\Sales\Models\OrderItem;
use EcomForYou\Sales\Models\Refund;
use EcomForYou\Sales\Models\RefundItem;

/**
 * Cache a path for a guest in the current scope, a second locale and currency, and a channel on its own host.
 */
function cachedEverywhere(string $path): array
{
    return [
        test()->cachePage($path),
        test()->cachePage($path, test()->secondScope),
        test()->cachePage($path, test()->otherHostScope, 'shop-two.test'),
    ];
}

/**
 * Assert none of the given cached copies survived.
 */
function assertNoneCached(array $requests): void
{
    foreach ($requests as $request) {
        test()->assertPageNotCached($request, 'A copy of '.$request->getPathInfo().' on '.$request->getHost().' survived.');
    }
}

beforeEach(function () {
    $this->useIsolatedPageCache();

    $this->otherHostScope = $this->addChannelOnHost('shop-two.test');

    $this->secondScope = $this->addSecondScope();

    $this->product = $this->createSimpleProduct();

    $this->productPath = '/'.$this->product->url_key;
});

// ============================================================================
// Reviews And Url Rewrites
// ============================================================================

it('should drop a product page everywhere when a review of it is updated or deleted', function () {
    $review = ProductReview::factory()->create(['product_id' => $this->product->id]);

    $copies = cachedEverywhere($this->productPath);

    app(ReviewListener::class)->afterUpdate($review);

    assertNoneCached($copies);

    $copies = cachedEverywhere($this->productPath);

    app(ReviewListener::class)->beforeDelete($review->id);

    assertNoneCached($copies);
});

it('should drop a rewritten path everywhere when its url rewrite is updated or deleted', function () {
    $urlRewrite = URLRewrite::factory()->create(['request_path' => 'old-summer-sale']);

    $copies = cachedEverywhere('/old-summer-sale');

    app(URLRewriteListener::class)->afterUpdate($urlRewrite);

    assertNoneCached($copies);

    $copies = cachedEverywhere('/old-summer-sale');

    app(URLRewriteListener::class)->beforeDelete($urlRewrite->id);

    assertNoneCached($copies);
});

// ============================================================================
// Orders And Refunds
// ============================================================================

it('should drop the pages of the ordered products everywhere when an order is placed or canceled', function () {
    $order = (new Order)->setRelation('all_items', collect([
        (new OrderItem)->setRelation('product', $this->product),
    ]));

    $copies = [...cachedEverywhere($this->productPath), ...cachedEverywhere('/')];

    app(OrderListener::class)->afterCancelOrCreate($order);

    assertNoneCached($copies);
});

it('should drop the pages of the refunded products everywhere when a refund is created', function () {
    $refund = (new Refund)->setRelation('items', collect([
        (new RefundItem)->setRelation('product', $this->product),
    ]));

    $copies = [...cachedEverywhere($this->productPath), ...cachedEverywhere('/')];

    app(RefundListener::class)->afterCreate($refund);

    assertNoneCached($copies);
});
