<?php

namespace EcomForYou\Sales\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Sales\Contracts\OrderAddress;

class OrderAddressRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return OrderAddress::class;
    }
}
