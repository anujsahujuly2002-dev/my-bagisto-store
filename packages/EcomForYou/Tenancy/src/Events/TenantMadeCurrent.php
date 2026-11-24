<?php

namespace EcomForYou\Tenancy\Events;

use EcomForYou\Tenancy\Models\Tenant;

class TenantMadeCurrent
{
    /**
     * Create a new event for the tenant that just became current.
     */
    public function __construct(public readonly Tenant $tenant) {}
}
