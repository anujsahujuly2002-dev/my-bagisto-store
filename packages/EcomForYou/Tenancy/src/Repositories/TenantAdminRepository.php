<?php

namespace EcomForYou\Tenancy\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tenancy\Contracts\TenantAdmin;

class TenantAdminRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return TenantAdmin::class;
    }
}
