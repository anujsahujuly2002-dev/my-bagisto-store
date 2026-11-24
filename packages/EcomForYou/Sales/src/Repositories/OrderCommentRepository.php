<?php

namespace EcomForYou\Sales\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Sales\Contracts\OrderComment;

class OrderCommentRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return OrderComment::class;
    }
}
