<?php

namespace EcomForYou\Checkout\Repositories;

use EcomForYou\Checkout\Contracts\CartPayment;
use EcomForYou\Core\Eloquent\Repository;

class CartPaymentRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CartPayment::class;
    }
}
