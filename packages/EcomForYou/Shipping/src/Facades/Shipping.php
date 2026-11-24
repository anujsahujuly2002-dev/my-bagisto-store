<?php

namespace EcomForYou\Shipping\Facades;

use EcomForYou\Shipping\Shipping as BaseShipping;
use Illuminate\Support\Facades\Facade;

class Shipping extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseShipping::class;
    }
}
