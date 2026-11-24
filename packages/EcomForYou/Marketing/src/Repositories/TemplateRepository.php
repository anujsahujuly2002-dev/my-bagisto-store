<?php

namespace EcomForYou\Marketing\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Marketing\Contracts\Template;

class TemplateRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return Template::class;
    }
}
