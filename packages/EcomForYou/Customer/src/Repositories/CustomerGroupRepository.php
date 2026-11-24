<?php

namespace EcomForYou\Customer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Customer\Contracts\CustomerGroup;

class CustomerGroupRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CustomerGroup::class;
    }
}
