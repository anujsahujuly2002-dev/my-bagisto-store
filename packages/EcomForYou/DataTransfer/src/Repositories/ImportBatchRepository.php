<?php

namespace EcomForYou\DataTransfer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\DataTransfer\Contracts\ImportBatch;

class ImportBatchRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ImportBatch::class;
    }
}
