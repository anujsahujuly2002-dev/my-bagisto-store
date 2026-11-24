<?php

use EcomForYou\Shipping\Facades\Shipping;

if (! function_exists('shipping')) {
    /**
     * Shipping helper.
     *
     * @return EcomForYou\Shipping\Shipping
     */
    function shipping()
    {
        return Shipping::getFacadeRoot();
    }
}
