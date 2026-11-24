<?php

namespace EcomForYou\CatalogRule\Repositories;

use EcomForYou\CatalogRule\Contracts\CatalogRuleProductPrice;
use EcomForYou\Core\Eloquent\Repository;

class CatalogRuleProductPriceRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CatalogRuleProductPrice::class;
    }
}
