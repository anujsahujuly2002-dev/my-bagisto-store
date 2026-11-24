<?php

namespace EcomForYou\Tax\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tax\Contracts\TaxMap;

class TaxMapRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return TaxMap::class;
    }
}
