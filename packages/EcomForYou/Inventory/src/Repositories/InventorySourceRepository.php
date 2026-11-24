<?php

namespace EcomForYou\Inventory\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Inventory\Contracts\InventorySource;

class InventorySourceRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return InventorySource::class;
    }
}
