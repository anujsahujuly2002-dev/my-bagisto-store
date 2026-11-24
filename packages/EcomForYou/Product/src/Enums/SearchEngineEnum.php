<?php

namespace EcomForYou\Product\Enums;

enum SearchEngineEnum: string
{
    /**
     * Database search driver.
     */
    case DATABASE = 'database';

    /**
     * Elasticsearch search driver.
     */
    case ELASTIC = 'elastic';
}
