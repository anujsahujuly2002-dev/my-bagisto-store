<?php

namespace EcomForYou\Customer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Customer\Contracts\CompareItem;

class CompareItemRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return CompareItem::class;
    }
}
