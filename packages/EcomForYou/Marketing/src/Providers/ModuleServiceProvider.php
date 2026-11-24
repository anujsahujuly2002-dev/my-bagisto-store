<?php

namespace EcomForYou\Marketing\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Marketing\Models\Campaign;
use EcomForYou\Marketing\Models\Event;
use EcomForYou\Marketing\Models\SearchSynonym;
use EcomForYou\Marketing\Models\SearchTerm;
use EcomForYou\Marketing\Models\Template;
use EcomForYou\Marketing\Models\URLRewrite;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Campaign::class,
        Event::class,
        SearchSynonym::class,
        SearchTerm::class,
        Template::class,
        URLRewrite::class,
    ];
}
