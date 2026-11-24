<?php

namespace EcomForYou\Sitemap\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Sitemap\Models\Sitemap;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Sitemap::class,
    ];
}
