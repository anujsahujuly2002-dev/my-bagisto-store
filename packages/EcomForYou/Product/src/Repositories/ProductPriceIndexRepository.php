<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductPriceIndex;

class ProductPriceIndexRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductPriceIndex::class;
    }
}
