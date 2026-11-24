<?php

namespace EcomForYou\Core\Facades;

use EcomForYou\Core\ElasticSearch as BaseElasticSearch;
use Illuminate\Support\Facades\Facade;

class ElasticSearch extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseElasticSearch::class;
    }
}
