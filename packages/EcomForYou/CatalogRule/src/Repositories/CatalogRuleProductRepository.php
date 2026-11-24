<?php

namespace EcomForYou\CatalogRule\Repositories;

use EcomForYou\CatalogRule\Contracts\CatalogRuleProduct;
use EcomForYou\Core\Eloquent\Repository;

class CatalogRuleProductRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CatalogRuleProduct::class;
    }
}
