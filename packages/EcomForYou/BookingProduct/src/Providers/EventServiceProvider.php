<?php

namespace EcomForYou\BookingProduct\Providers;

use EcomForYou\BookingProduct\Listeners\Order;
use EcomForYou\BookingProduct\Listeners\PriceNote;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'checkout.order.save.after' => [
            [Order::class, 'afterPlaceOrder'],
        ],

        'bagisto.shop.products.price.after' => [
            [PriceNote::class, 'addNote'],
        ],
    ];
}
