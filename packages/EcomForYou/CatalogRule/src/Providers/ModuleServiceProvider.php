<?php

namespace EcomForYou\CatalogRule\Providers;

use EcomForYou\CatalogRule\Models\CatalogRule;
use EcomForYou\CatalogRule\Models\CatalogRuleProduct;
use EcomForYou\CatalogRule\Models\CatalogRuleProductPrice;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        CatalogRule::class,
        CatalogRuleProduct::class,
        CatalogRuleProductPrice::class,
    ];
}
