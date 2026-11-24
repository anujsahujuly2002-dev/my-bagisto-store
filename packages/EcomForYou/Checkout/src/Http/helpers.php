<?php

use EcomForYou\Checkout\Facades\Cart;

if (! function_exists('cart')) {
    /**
     * Cart helper.
     *
     * @return EcomForYou\Checkout\Cart
     */
    function cart()
    {
        return Cart::getFacadeRoot();
    }
}
