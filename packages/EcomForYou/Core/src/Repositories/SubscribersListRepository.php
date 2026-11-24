<?php

namespace EcomForYou\Core\Repositories;

use EcomForYou\Core\Contracts\SubscribersList;
use EcomForYou\Core\Eloquent\Repository;

class SubscribersListRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return SubscribersList::class;
    }

    /**
     * Delete a slider item and delete the image from the disk or where ever it is.
     *
     * @param  int  $id
     * @return bool
     */
    public function destroy($id)
    {
        return $this->model->destroy($id);
    }
}
