<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Product\Contracts\ProductVideo;

class ProductVideoRepository extends ProductMediaRepository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductVideo::class;
    }
}
