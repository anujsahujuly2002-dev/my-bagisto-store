<?php

namespace EcomForYou\Checkout\Facades;

use EcomForYou\Checkout\Cart as BaseCart;
use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseCart::class;
    }
}
