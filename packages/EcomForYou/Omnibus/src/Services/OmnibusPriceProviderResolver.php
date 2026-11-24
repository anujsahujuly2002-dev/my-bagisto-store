<?php

namespace EcomForYou\Omnibus\Services;

use EcomForYou\Omnibus\Contracts\OmnibusPriceProvider;
use EcomForYou\Omnibus\PriceProviders\DefaultOmnibusPriceProvider;
use EcomForYou\Product\Contracts\Product;
use Illuminate\Contracts\Container\Container;

class OmnibusPriceProviderResolver
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Resolve the Omnibus price provider for the given product.
     */
    public function resolve(Product $product): OmnibusPriceProvider
    {
        $class = config("omnibus.providers.types.{$product->type}")
            ?? config('omnibus.providers.default', DefaultOmnibusPriceProvider::class);

        return $this->container->make($class);
    }
}
