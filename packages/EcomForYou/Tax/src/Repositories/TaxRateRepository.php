<?php

namespace EcomForYou\Tax\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tax\Contracts\TaxRate;

class TaxRateRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return TaxRate::class;
    }
}
