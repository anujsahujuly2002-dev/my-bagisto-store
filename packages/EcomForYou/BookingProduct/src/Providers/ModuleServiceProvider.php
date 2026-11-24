<?php

namespace EcomForYou\BookingProduct\Providers;

use EcomForYou\BookingProduct\Models\Booking;
use EcomForYou\BookingProduct\Models\BookingProduct;
use EcomForYou\BookingProduct\Models\BookingProductAppointmentSlot;
use EcomForYou\BookingProduct\Models\BookingProductDefaultSlot;
use EcomForYou\BookingProduct\Models\BookingProductEventTicket;
use EcomForYou\BookingProduct\Models\BookingProductEventTicketTranslation;
use EcomForYou\BookingProduct\Models\BookingProductRentalSlot;
use EcomForYou\BookingProduct\Models\BookingProductTableSlot;
use EcomForYou\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        BookingProduct::class,
        BookingProductDefaultSlot::class,
        BookingProductAppointmentSlot::class,
        BookingProductEventTicket::class,
        BookingProductEventTicketTranslation::class,
        BookingProductRentalSlot::class,
        BookingProductTableSlot::class,
        Booking::class,
    ];
}
