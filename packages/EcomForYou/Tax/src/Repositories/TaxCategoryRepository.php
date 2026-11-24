<?php

namespace EcomForYou\Tax\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tax\Contracts\TaxCategory;

class TaxCategoryRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return TaxCategory::class;
    }

    /**
     * Get the configuration options.
     */
    public function getConfigOptions(): array
    {
        $options = [
            [
                'title' => 'admin::app.configuration.index.sales.taxes.categories.none',
                'value' => 0,
            ],
        ];

        foreach ($this->all() as $taxCategory) {
            $options[] = [
                'title' => $taxCategory->name,
                'value' => $taxCategory->id,
            ];
        }

        return $options;
    }
}
