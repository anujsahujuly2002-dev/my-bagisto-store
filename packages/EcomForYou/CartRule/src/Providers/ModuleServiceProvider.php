<?php

namespace EcomForYou\CartRule\Providers;

use EcomForYou\CartRule\Models\CartRule;
use EcomForYou\CartRule\Models\CartRuleCoupon;
use EcomForYou\CartRule\Models\CartRuleCouponUsage;
use EcomForYou\CartRule\Models\CartRuleCustomer;
use EcomForYou\CartRule\Models\CartRuleTranslation;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        CartRule::class,
        CartRuleCoupon::class,
        CartRuleCouponUsage::class,
        CartRuleCustomer::class,
        CartRuleTranslation::class,
    ];
}
