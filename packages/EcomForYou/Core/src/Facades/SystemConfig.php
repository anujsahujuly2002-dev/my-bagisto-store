<?php

namespace EcomForYou\Core\Facades;

use EcomForYou\Core\SystemConfig as BaseSystemConfig;
use Illuminate\Support\Facades\Facade;

class SystemConfig extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseSystemConfig::class;
    }
}
