<?php

namespace EcomForYou\Tenancy\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tenancy\Contracts\Plan;

class PlanRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Plan::class;
    }
}
