<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductInventoryIndex;

class ProductInventoryIndexRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductInventoryIndex::class;
    }
}
