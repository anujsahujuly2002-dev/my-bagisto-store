<?php

namespace EcomForYou\DataTransfer\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\DataTransfer\Models\Import;
use EcomForYou\DataTransfer\Models\ImportBatch;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Import::class,
        ImportBatch::class,
    ];
}
