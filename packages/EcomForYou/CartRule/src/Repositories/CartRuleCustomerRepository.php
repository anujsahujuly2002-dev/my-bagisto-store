<?php

namespace EcomForYou\CartRule\Repositories;

use EcomForYou\CartRule\Contracts\CartRuleCustomer;
use EcomForYou\Core\Eloquent\Repository;

class CartRuleCustomerRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CartRuleCustomer::class;
    }
}
