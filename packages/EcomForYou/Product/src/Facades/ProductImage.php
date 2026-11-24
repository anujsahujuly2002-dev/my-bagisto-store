<?php

namespace EcomForYou\Product\Facades;

use EcomForYou\Product\ProductImage as BaseProductImage;
use Illuminate\Support\Facades\Facade;

class ProductImage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseProductImage::class;
    }
}
