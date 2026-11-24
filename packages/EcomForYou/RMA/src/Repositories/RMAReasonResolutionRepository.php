<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAReasonResolution;

class RMAReasonResolutionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAReasonResolution::class;
    }
}
