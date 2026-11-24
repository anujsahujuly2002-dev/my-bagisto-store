<?php

namespace EcomForYou\Omnibus\Listeners;

use EcomForYou\Omnibus\Services\OmnibusPriceManager;
use EcomForYou\Product\Contracts\Product;

class ProductPriceChange
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected OmnibusPriceManager $omnibusPriceManager
    ) {}

    /**
     * Record a price snapshot after a product is created or updated.
     */
    public function handle(Product $product): void
    {
        $this->omnibusPriceManager->recordPrice($product);
    }
}
