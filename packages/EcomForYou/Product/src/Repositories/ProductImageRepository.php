<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Product\Contracts\ProductImage;

class ProductImageRepository extends ProductMediaRepository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductImage::class;
    }
}
