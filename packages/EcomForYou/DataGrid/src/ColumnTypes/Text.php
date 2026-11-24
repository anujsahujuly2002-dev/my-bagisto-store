<?php

namespace EcomForYou\DataGrid\ColumnTypes;

use EcomForYou\DataGrid\Column;
use EcomForYou\DataGrid\Enums\FilterTypeEnum;
use EcomForYou\DataGrid\Exceptions\InvalidColumnExpressionException;

class Text extends Column
{
    /**
     * Process filter.
     */
    public function processFilter($queryBuilder, $requestedValues)
    {
        if ($this->filterableType === FilterTypeEnum::DROPDOWN->value) {
            return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
                if (is_string($requestedValues)) {
                    $scopeQueryBuilder->orWhere($this->columnName, $requestedValues);
                } elseif (is_array($requestedValues)) {
                    foreach ($requestedValues as $value) {
                        $scopeQueryBuilder->orWhere($this->columnName, $value);
                    }
                } else {
                    throw new InvalidColumnExpressionException('Only string and array are allowed for text column type.');
                }
            });
        }

        return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
            if (is_string($requestedValues)) {
                $scopeQueryBuilder->orWhere($this->columnName, $this->likeOperator(), '%'.$requestedValues.'%');
            } elseif (is_array($requestedValues)) {
                foreach ($requestedValues as $value) {
                    $scopeQueryBuilder->orWhere($this->columnName, $this->likeOperator(), '%'.$value.'%');
                }
            } else {
                throw new InvalidColumnExpressionException('Only string and array are allowed for text column type.');
            }
        });
    }
}
