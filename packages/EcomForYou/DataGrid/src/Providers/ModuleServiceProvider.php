<?php

namespace EcomForYou\DataGrid\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\DataGrid\Models\SavedFilter;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        SavedFilter::class,
    ];
}
