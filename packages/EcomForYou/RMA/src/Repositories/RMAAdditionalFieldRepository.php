<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAAdditionalField;

class RMAAdditionalFieldRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAAdditionalField::class;
    }

    /**
     * Create many additional fields for RMA.
     */
    public function createManyForRma(int $rmaId, array $customAttributes): void
    {
        foreach ($customAttributes as $key => $customAttribute) {
            $customAttributesData = [
                'rma_id' => $rmaId,
                'rma_custom_field_id' => $key,
                'value' => is_array($customAttribute) ? implode(',', $customAttribute) : $customAttribute,
            ];

            $this->create($customAttributesData);
        }
    }
}
