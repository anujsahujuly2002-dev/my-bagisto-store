<?php

namespace EcomForYou\Customer\Facades;

use EcomForYou\Customer\AddressSettings as BaseAddressSettings;
use Illuminate\Support\Facades\Facade;

class AddressSettings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BaseAddressSettings::class;
    }
}
