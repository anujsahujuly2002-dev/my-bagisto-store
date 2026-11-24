<?php

namespace EcomForYou\Checkout\Providers;

use EcomForYou\Checkout\Models\Cart;
use EcomForYou\Checkout\Models\CartAddress;
use EcomForYou\Checkout\Models\CartItem;
use EcomForYou\Checkout\Models\CartPayment;
use EcomForYou\Checkout\Models\CartShippingRate;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Cart::class,
        CartAddress::class,
        CartItem::class,
        CartPayment::class,
        CartShippingRate::class,
    ];
}
