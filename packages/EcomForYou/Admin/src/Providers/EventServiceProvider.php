<?php

namespace EcomForYou\Admin\Providers;

use EcomForYou\Admin\Listeners\Admin;
use EcomForYou\Admin\Listeners\Customer;
use EcomForYou\Admin\Listeners\ExchangeRate;
use EcomForYou\Admin\Listeners\GDPR;
use EcomForYou\Admin\Listeners\Invoice;
use EcomForYou\Admin\Listeners\Notification;
use EcomForYou\Admin\Listeners\Order;
use EcomForYou\Admin\Listeners\Refund;
use EcomForYou\Admin\Listeners\Shipment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'customer.create.after' => [
            [Customer::class, 'afterCreated'],
        ],

        'customer.gdpr-request.create.after' => [
            [GDPR::class, 'afterGdprRequestCreated'],
        ],

        'customer.gdpr-request.update.after' => [
            [GDPR::class, 'afterGdprRequestUpdated'],
        ],

        'admin.password.update.after' => [
            [Admin::class, 'afterPasswordUpdated'],
        ],

        'checkout.order.save.after' => [
            [Order::class, 'afterCreated'],
        ],

        'sales.order.cancel.after' => [
            [Order::class, 'afterCanceled'],
        ],

        'sales.invoice.save.after' => [
            [Invoice::class, 'afterCreated'],
        ],

        'sales.shipment.save.after' => [
            [Shipment::class, 'afterCreated'],
        ],

        'sales.refund.save.after' => [
            [Refund::class, 'afterCreated'],
        ],

        'notification.create.after' => [
            [Notification::class, 'afterCreated'],
        ],

        'core.exchange_rate.update_rates.after' => [
            [ExchangeRate::class, 'afterRatesUpdated'],
        ],
    ];
}
