<?php

namespace EcomForYou\Inventory\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Inventory\Models\InventorySource;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        InventorySource::class,
    ];
}
