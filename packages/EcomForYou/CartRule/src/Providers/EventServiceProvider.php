<?php

namespace EcomForYou\CartRule\Providers;

use EcomForYou\CartRule\Listeners\Cart;
use EcomForYou\CartRule\Listeners\Order;
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
            [Order::class, 'manageCartRule'],
        ],

        'checkout.cart.collect.totals.before' => [
            [Cart::class, 'applyCartRules'],
        ],
    ];
}
