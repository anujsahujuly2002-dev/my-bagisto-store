<?php

namespace EcomForYou\DataGrid\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\DataGrid\Contracts\SavedFilter;

class SavedFilterRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return SavedFilter::class;
    }
}
