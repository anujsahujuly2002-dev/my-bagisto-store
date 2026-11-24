<?php

namespace EcomForYou\Category\Providers;

use EcomForYou\Category\Models\Category;
use EcomForYou\Category\Models\CategoryTranslation;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Category::class,
        CategoryTranslation::class,
    ];
}
