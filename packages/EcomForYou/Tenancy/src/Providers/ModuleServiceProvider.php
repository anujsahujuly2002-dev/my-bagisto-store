<?php

namespace EcomForYou\Tenancy\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Tenancy\Models\Plan;
use EcomForYou\Tenancy\Models\PlanUsage;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Models\TenantAdmin;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models registered with Concord for this module.
     *
     * @var class-string[]
     */
    protected $models = [
        Plan::class,
        PlanUsage::class,
        Tenant::class,
        TenantAdmin::class,
    ];
}
