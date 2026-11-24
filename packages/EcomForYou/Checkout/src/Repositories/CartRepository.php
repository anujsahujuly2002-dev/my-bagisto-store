<?php

namespace EcomForYou\Checkout\Repositories;

use EcomForYou\Checkout\Contracts\Cart;
use EcomForYou\Core\Eloquent\Repository;

class CartRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return Cart::class;
    }
}
