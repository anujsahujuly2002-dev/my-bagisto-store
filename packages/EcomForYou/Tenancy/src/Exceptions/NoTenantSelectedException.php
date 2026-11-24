<?php

namespace EcomForYou\Tenancy\Exceptions;

use RuntimeException;

class NoTenantSelectedException extends RuntimeException
{
    /**
     * Create the exception for store data reached before any tenant was made current.
     */
    public function __construct(string $resource = 'database')
    {
        parent::__construct("No tenant selected: the store {$resource} cannot be reached until a tenant is current.");
    }
}
