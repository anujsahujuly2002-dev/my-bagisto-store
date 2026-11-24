<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductFlat;

class ProductFlatRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductFlat::class;
    }
}
