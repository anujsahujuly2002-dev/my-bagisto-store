<?php

namespace EcomForYou\Payment\Providers;

use EcomForYou\Payment\Listeners\GenerateInvoice;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('checkout.order.save.after', [GenerateInvoice::class, 'handle']);
    }
}
