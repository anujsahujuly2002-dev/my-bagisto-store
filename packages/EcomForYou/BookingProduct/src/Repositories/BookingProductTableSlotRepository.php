<?php

namespace EcomForYou\BookingProduct\Repositories;

use EcomForYou\BookingProduct\Contracts\BookingProductTableSlot;
use EcomForYou\Core\Eloquent\Repository;

class BookingProductTableSlotRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return BookingProductTableSlot::class;
    }
}
