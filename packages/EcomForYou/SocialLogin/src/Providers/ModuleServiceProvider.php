<?php

namespace EcomForYou\SocialLogin\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\SocialLogin\Models\CustomerSocialAccount;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        CustomerSocialAccount::class,
    ];
}
