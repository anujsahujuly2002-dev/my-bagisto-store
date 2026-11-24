<?php

namespace EcomForYou\Tax\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Tax\Models\TaxCategory;
use EcomForYou\Tax\Models\TaxMap;
use EcomForYou\Tax\Models\TaxRate;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        TaxCategory::class,
        TaxMap::class,
        TaxRate::class,
    ];
}
