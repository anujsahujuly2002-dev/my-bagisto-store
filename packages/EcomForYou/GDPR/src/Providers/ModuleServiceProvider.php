<?php

namespace EcomForYou\GDPR\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\GDPR\Models\GDPRDataRequest;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        GDPRDataRequest::class,
    ];
}
