<?php

namespace EcomForYou\Customer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Customer\Contracts\Wishlist;

class WishlistRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Wishlist::class;
    }
}
