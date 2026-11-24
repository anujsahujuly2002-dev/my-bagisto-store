<?php

namespace EcomForYou\CMS\Providers;

use EcomForYou\CMS\Models\Page;
use EcomForYou\CMS\Models\PageTranslation;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Page::class,
        PageTranslation::class,
    ];
}
