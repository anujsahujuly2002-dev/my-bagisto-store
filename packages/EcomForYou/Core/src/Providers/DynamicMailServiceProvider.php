<?php

namespace EcomForYou\Core\Providers;

use EcomForYou\Core\Mail\Transport\DynamicMailTransport;
use Illuminate\Support\ServiceProvider;

class DynamicMailServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->app['mail.manager']->extend('bagisto-dynamic-smtp', function () {
            return new DynamicMailTransport;
        });
    }
}
