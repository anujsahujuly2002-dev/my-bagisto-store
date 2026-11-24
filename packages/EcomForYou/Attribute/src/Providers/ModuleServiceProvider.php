<?php

namespace EcomForYou\Attribute\Providers;

use EcomForYou\Attribute\Models\Attribute;
use EcomForYou\Attribute\Models\AttributeFamily;
use EcomForYou\Attribute\Models\AttributeGroup;
use EcomForYou\Attribute\Models\AttributeGroupTranslation;
use EcomForYou\Attribute\Models\AttributeOption;
use EcomForYou\Attribute\Models\AttributeOptionTranslation;
use EcomForYou\Attribute\Models\AttributeTranslation;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Attribute::class,
        AttributeFamily::class,
        AttributeGroup::class,
        AttributeGroupTranslation::class,
        AttributeOption::class,
        AttributeOptionTranslation::class,
        AttributeTranslation::class,
    ];
}
