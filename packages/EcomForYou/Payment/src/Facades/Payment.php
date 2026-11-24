<?php

namespace EcomForYou\Payment\Facades;

use EcomForYou\Payment\Payment as BasePayment;
use Illuminate\Support\Facades\Facade;

class Payment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BasePayment::class;
    }
}
