<?php

namespace EcomForYou\BookingProduct\Repositories;

use EcomForYou\BookingProduct\Contracts\BookingProductRentalSlot;
use EcomForYou\Core\Eloquent\Repository;

class BookingProductRentalSlotRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return BookingProductRentalSlot::class;
    }
}
