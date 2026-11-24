<?php

namespace EcomForYou\Core\Repositories;

use EcomForYou\Core\Contracts\CurrencyExchangeRate;
use EcomForYou\Core\Eloquent\Repository;

class ExchangeRateRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CurrencyExchangeRate::class;
    }
}
