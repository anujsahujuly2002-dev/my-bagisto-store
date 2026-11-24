<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMACustomField;

class RMACustomFieldRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMACustomField::class;
    }
}
