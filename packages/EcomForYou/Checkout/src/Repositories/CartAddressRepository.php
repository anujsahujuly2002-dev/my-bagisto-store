<?php

namespace EcomForYou\Checkout\Repositories;

use EcomForYou\Checkout\Contracts\CartAddress;
use EcomForYou\Core\Eloquent\Repository;

class CartAddressRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CartAddress::class;
    }
}
