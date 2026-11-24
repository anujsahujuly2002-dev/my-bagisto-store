<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;

interface SwitchingTask
{
    /**
     * Point this part of the application at the given tenant.
     */
    public function makeCurrent(Tenant $tenant): void;

    /**
     * Leave no tenant current: lock this part down while tenancy is on, or restore it to the single store while it is off.
     */
    public function forgetCurrent(): void;
}
