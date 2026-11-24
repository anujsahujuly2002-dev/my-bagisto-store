<?php

namespace EcomForYou\CartRule\Listeners;

use EcomForYou\CartRule\Helpers\CartRule;

class Cart
{
    /**
     * Create a new listener instance.
     *
     * @param  \EcomForYou\CartRule\Repositories\CartRule  $cartRuleHelper
     * @return void
     */
    public function __construct(protected CartRule $cartRuleHelper) {}

    /**
     * Apply valid cart rules to cart
     *
     * @param  \EcomForYou\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function applyCartRules($cart)
    {
        $this->cartRuleHelper->collect($cart);
    }
}
