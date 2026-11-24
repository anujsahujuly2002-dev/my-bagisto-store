<?php

namespace EcomForYou\Attribute\Repositories;

use EcomForYou\Attribute\Contracts\AttributeGroup;
use EcomForYou\Core\Eloquent\Repository;

class AttributeGroupRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return AttributeGroup::class;
    }
}
