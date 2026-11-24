<?php

namespace EcomForYou\Core\Facades;

use EcomForYou\Core\Menu as BaseMenu;
use Illuminate\Support\Facades\Facade;

class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseMenu::class;
    }
}
