<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMARule;

class RMARuleRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMARule::class;
    }

    /**
     * Get all active RMA rules.
     */
    public function getActiveRules()
    {
        return $this->model->where('status', 1)->get();
    }
}
