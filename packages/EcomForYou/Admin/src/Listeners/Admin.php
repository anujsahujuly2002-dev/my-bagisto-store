<?php

namespace EcomForYou\Admin\Listeners;

class Admin
{
    /**
     * Send mail on updating password.
     *
     * @param  \EcomForYou\User\Models\Admin  $admin
     * @return void
     */
    public function afterPasswordUpdated($admin) {}
}
