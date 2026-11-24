<?php

namespace EcomForYou\User\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\User\Models\Admin;
use EcomForYou\User\Models\Role;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Admin::class,
        Role::class,
    ];
}
