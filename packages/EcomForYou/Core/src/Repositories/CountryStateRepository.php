<?php

namespace EcomForYou\Core\Repositories;

use EcomForYou\Core\Contracts\CountryState;
use EcomForYou\Core\Eloquent\Repository;

class CountryStateRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CountryState::class;
    }
}
