<?php

use EcomForYou\MagicAI\Facades\MagicAI;

if (! function_exists('magic_ai')) {
    /**
     * MagicAI helper.
     *
     * @return EcomForYou\MagicAI\MagicAI
     */
    function magic_ai()
    {
        return MagicAI::getFacadeRoot();
    }
}
