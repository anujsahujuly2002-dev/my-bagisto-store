<?php

namespace EcomForYou\Customer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Customer\Contracts\CustomerAddress;

class CustomerAddressRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return CustomerAddress::class;
    }
}
