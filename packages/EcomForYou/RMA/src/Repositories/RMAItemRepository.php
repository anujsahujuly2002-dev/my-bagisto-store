<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAItem;

class RMAItemRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAItem::class;
    }
}
