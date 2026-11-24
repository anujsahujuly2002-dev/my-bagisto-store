<?php

namespace EcomForYou\DataTransfer\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\DataTransfer\Contracts\Import;

class ImportRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Import::class;
    }

    /**
     * Move an import to a new state, but only while it is still in one of the
     * states given. The check and the write are one statement, so of two
     * requests arriving together exactly one gets `true` back.
     */
    public function transitionState(int $id, array $from, string $to, array $attributes = []): bool
    {
        return (bool) $this->model
            ->newQuery()
            ->where('id', $id)
            ->whereIn('state', $from)
            ->update(array_merge($attributes, ['state' => $to]));
    }
}
