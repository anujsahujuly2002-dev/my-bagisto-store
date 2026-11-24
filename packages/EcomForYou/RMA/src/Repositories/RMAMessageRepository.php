<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAMessage;

class RMAMessageRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAMessage::class;
    }
}
