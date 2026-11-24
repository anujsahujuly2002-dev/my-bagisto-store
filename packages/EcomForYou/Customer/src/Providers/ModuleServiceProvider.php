<?php

namespace EcomForYou\Customer\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Customer\Models\CompareItem;
use EcomForYou\Customer\Models\Customer;
use EcomForYou\Customer\Models\CustomerAddress;
use EcomForYou\Customer\Models\CustomerGroup;
use EcomForYou\Customer\Models\CustomerNote;
use EcomForYou\Customer\Models\Wishlist;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        CompareItem::class,
        Customer::class,
        CustomerAddress::class,
        CustomerGroup::class,
        CustomerNote::class,
        Wishlist::class,
    ];
}
