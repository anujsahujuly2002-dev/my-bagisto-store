<?php

namespace EcomForYou\Tenancy\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tenancy\Contracts\PlanUsage;

class PlanUsageRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PlanUsage::class;
    }
}
