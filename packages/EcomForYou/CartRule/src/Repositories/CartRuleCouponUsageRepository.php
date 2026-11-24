<?php

namespace EcomForYou\CartRule\Repositories;

use EcomForYou\CartRule\Contracts\CartRuleCouponUsage;
use EcomForYou\Core\Eloquent\Repository;

class CartRuleCouponUsageRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CartRuleCouponUsage::class;
    }
}
