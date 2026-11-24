<?php

namespace EcomForYou\MagicAI\Facades;

use EcomForYou\MagicAI\MagicAI as BaseMagicAI;
use Illuminate\Support\Facades\Facade;

class MagicAI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseMagicAI::class;
    }
}
