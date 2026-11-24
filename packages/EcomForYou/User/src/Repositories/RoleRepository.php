<?php

namespace EcomForYou\User\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\User\Contracts\Role;

class RoleRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return Role::class;
    }
}
