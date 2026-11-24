<?php

namespace EcomForYou\Theme\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Theme\Models\Section;
use EcomForYou\Theme\Models\SectionTranslation;
use EcomForYou\Theme\Models\Theme;
use EcomForYou\Theme\Models\ThemeSchedule;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Define the models.
     *
     * @var array
     */
    protected $models = [
        Section::class,
        SectionTranslation::class,
        Theme::class,
        ThemeSchedule::class,
    ];
}
