<?php

namespace EcomForYou\Customer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Customer\Contracts\CustomerNote;

class CustomerNoteRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return CustomerNote::class;
    }
}
