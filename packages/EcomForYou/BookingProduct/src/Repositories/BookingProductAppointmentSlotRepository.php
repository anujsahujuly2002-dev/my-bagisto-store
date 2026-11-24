<?php

namespace EcomForYou\BookingProduct\Repositories;

use EcomForYou\BookingProduct\Contracts\BookingProductAppointmentSlot;
use EcomForYou\Core\Eloquent\Repository;

class BookingProductAppointmentSlotRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return BookingProductAppointmentSlot::class;
    }
}
