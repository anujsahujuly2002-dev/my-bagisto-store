<?php

namespace EcomForYou\Marketing\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Marketing\Contracts\URLRewrite;

class URLRewriteRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return URLRewrite::class;
    }
}
