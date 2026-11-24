<?php

namespace EcomForYou\Product\Facades;

use EcomForYou\Product\ProductVideo as BaseProductVideo;
use Illuminate\Support\Facades\Facade;

class ProductVideo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseProductVideo::class;
    }
}
