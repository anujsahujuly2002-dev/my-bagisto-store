<?php

namespace EcomForYou\Marketing\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Marketing\Contracts\Event;

class EventRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return Event::class;
    }
}
