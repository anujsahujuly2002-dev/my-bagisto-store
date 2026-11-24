<?php

namespace EcomForYou\Tenancy\Events;

use EcomForYou\Tenancy\Models\Tenant;

class TenantForgotten
{
    /**
     * Create a new event for the tenant that just stopped being current.
     */
    public function __construct(public readonly Tenant $tenant) {}
}
