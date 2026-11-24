<?php

namespace EcomForYou\BookingProduct\Listeners;

use EcomForYou\BookingProduct\Repositories\BookingRepository;

class Order
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected BookingRepository $bookingRepository) {}

    /**
     * After sales order creation, add entry to bookings table
     *
     * @param  \EcomForYou\Sales\Contracts\Order  $order
     */
    public function afterPlaceOrder($order)
    {
        $this->bookingRepository->create(['order' => $order]);
    }
}
