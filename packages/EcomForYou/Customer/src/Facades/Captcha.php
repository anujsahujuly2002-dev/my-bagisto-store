<?php

namespace EcomForYou\Customer\Facades;

use EcomForYou\Customer\Captcha as BaseCaptcha;
use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseCaptcha::class;
    }
}
