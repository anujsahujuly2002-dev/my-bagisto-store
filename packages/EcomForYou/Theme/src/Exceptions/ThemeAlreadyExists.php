<?php

namespace EcomForYou\Theme\Exceptions;

use EcomForYou\Theme\Theme;

class ThemeAlreadyExists extends \Exception
{
    /**
     * Create an instance.
     *
     * @param  Theme  $theme
     * @return void
     */
    public function __construct($theme)
    {
        parent::__construct("Theme {$theme->name} already exists", 1);
    }
}
