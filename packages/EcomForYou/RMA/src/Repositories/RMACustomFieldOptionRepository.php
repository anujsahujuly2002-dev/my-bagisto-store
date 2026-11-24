<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMACustomFieldOption;

class RMACustomFieldOptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMACustomFieldOption::class;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createOption(array $dataOptions, int $rmaCustomFieldId): void
    {
        foreach ($dataOptions['options'] as $key => $option) {
            $this->model->create([
                'rma_custom_field_id' => $rmaCustomFieldId,
                'name' => $option,
                'value' => $dataOptions['value'][$key],
            ]);
        }
    }
}
