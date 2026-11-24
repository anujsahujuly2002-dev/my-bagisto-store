<?php

namespace EcomForYou\Omnibus\Providers;

use EcomForYou\Omnibus\Models\OmnibusPrice;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        OmnibusPrice::class,
    ];
}
