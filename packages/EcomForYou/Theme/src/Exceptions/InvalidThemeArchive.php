<?php

namespace EcomForYou\Theme\Exceptions;

use Exception;

class InvalidThemeArchive extends Exception
{
    /**
     * Create a new exception for the reason an uploaded theme was refused, with the values its message names.
     *
     * @return void
     */
    public function __construct(
        public readonly string $reason,
        public readonly array $replace = []
    ) {
        parent::__construct('The theme archive was refused: '.$reason.'.');
    }
}
