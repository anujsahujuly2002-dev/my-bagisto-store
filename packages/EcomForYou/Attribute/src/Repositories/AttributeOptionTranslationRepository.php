<?php

namespace EcomForYou\Attribute\Repositories;

use EcomForYou\Attribute\Contracts\AttributeOptionTranslation;
use EcomForYou\Core\Eloquent\Repository;

class AttributeOptionTranslationRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return AttributeOptionTranslation::class;
    }
}
