<?php

namespace EcomForYou\BookingProduct\Repositories;

use EcomForYou\BookingProduct\Contracts\BookingProductDefaultSlot;
use EcomForYou\Core\Eloquent\Repository;

class BookingProductDefaultSlotRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return BookingProductDefaultSlot::class;
    }
}
