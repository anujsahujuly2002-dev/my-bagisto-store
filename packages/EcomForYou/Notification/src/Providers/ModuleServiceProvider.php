<?php

namespace EcomForYou\Notification\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Notification\Models\Notification;
use EcomForYou\Notification\Models\NotificationRead;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Notification::class,
        NotificationRead::class,
    ];
}
