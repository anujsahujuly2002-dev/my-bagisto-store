<?php

namespace Webkul\Theme\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Models\SectionTranslation;
use Webkul\Theme\Models\Theme;
use Webkul\Theme\Models\ThemeSchedule;

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
