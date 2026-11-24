<?php

namespace EcomForYou\Product\Enums;

enum SearchContextEnum: string
{
    /**
     * Storefront (shop) search context.
     */
    case STOREFRONT = 'storefront';

    /**
     * Admin panel search context.
     */
    case ADMIN = 'admin';
}
