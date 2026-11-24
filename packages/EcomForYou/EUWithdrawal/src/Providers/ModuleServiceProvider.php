<?php

namespace EcomForYou\EUWithdrawal\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\EUWithdrawal\Models\Withdrawal;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models registered with Concord for this module.
     *
     * @var class-string[]
     */
    protected $models = [
        Withdrawal::class,
    ];
}
