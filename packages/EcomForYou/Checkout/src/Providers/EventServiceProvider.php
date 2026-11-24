<?php

namespace EcomForYou\Checkout\Providers;

use EcomForYou\Checkout\Listeners\CustomerEventsHandler;
use EcomForYou\Checkout\Listeners\OrderEventsHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        CustomerEventsHandler::class,
        OrderEventsHandler::class,
    ];
}
