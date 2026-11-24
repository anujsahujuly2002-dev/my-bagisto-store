<?php

namespace EcomForYou\Marketing\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Marketing\Contracts\Campaign;

class CampaignRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return Campaign::class;
    }
}
