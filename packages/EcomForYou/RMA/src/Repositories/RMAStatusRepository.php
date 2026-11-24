<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAStatus;

class RMAStatusRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAStatus::class;
    }
}
