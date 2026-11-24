<?php

use EcomForYou\Admin\Notifications\AdminNotifications;
use EcomForYou\Admin\Notifications\Types\AppUrlMismatch;
use EcomForYou\Admin\Notifications\Types\BroadcastUnreachable;
use EcomForYou\Admin\Notifications\Types\CustomerRegistered;
use EcomForYou\Admin\Notifications\Types\DebugModeEnabled;
use EcomForYou\Admin\Notifications\Types\ExchangeRatesStale;
use EcomForYou\Admin\Notifications\Types\FailedJobs;
use EcomForYou\Admin\Notifications\Types\GDPRRequested;
use EcomForYou\Admin\Notifications\Types\ImportCompleted;
use EcomForYou\Admin\Notifications\Types\IndexIncomplete;
use EcomForYou\Admin\Notifications\Types\MailNotConfigured;
use EcomForYou\Admin\Notifications\Types\OrderCanceled;
use EcomForYou\Admin\Notifications\Types\OrderPlaced;
use EcomForYou\Admin\Notifications\Types\OrderRefunded;
use EcomForYou\Admin\Notifications\Types\QueueWorkerStopped;
use EcomForYou\Admin\Notifications\Types\ReturnRequested;
use EcomForYou\Admin\Notifications\Types\ReviewCreated;
use EcomForYou\Admin\Notifications\Types\SchedulerStopped;
use EcomForYou\Admin\Notifications\Types\SearchUnreachable;
use EcomForYou\Admin\Notifications\Types\StorageUnreachable;

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    |
    | The notification types the admin panel ships with.
    |
    */

    'order.placed' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'orders',
        'class' => OrderPlaced::class,
        'permission' => 'sales.orders',
        'events' => ['checkout.order.save.after'],
    ],

    'order.canceled' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'orders',
        'class' => OrderCanceled::class,
        'permission' => 'sales.orders',
        'events' => ['sales.order.cancel.after'],
    ],

    'order.refunded' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'orders',
        'class' => OrderRefunded::class,
        'permission' => 'sales.refunds',
        'events' => ['sales.refund.save.after'],
    ],

    'customer.registered' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'customers',
        'class' => CustomerRegistered::class,
        'permission' => 'customers.customers',
        'events' => ['customer.registration.after'],
    ],

    'review.created' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'reviews',
        'class' => ReviewCreated::class,
        'permission' => 'customers.reviews',
        'events' => ['customer.review.create.after'],
    ],

    'return.requested' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'returns',
        'class' => ReturnRequested::class,
        'permission' => 'sales.rma.requests',
        'events' => ['customer.rma.request.create.after'],
    ],

    'gdpr.requested' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'gdpr',
        'class' => GDPRRequested::class,
        'permission' => 'customers.gdpr_requests',
        'events' => ['customer.gdpr-request.create.after'],
    ],

    'import.completed' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'imports',
        'class' => ImportCompleted::class,
        'permission' => 'settings.data_transfer.imports',
        'events' => ['data_transfer.imports.completed'],
    ],

    'system.queue_worker_stopped' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => QueueWorkerStopped::class,
        'permission' => 'configuration',
    ],

    'system.failed_jobs' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => FailedJobs::class,
        'permission' => 'configuration',
    ],

    'system.scheduler_stopped' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => SchedulerStopped::class,
        'permission' => 'configuration',
    ],

    'system.mail_not_configured' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => MailNotConfigured::class,
        'permission' => 'configuration',
    ],

    'system.debug_mode_enabled' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => DebugModeEnabled::class,
        'permission' => 'configuration',
    ],

    'system.app_url_mismatch' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => AppUrlMismatch::class,
        'permission' => 'configuration',
    ],

    'system.search_unreachable' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => SearchUnreachable::class,
        'permission' => 'configuration',
    ],

    'system.storage_unreachable' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => StorageUnreachable::class,
        'permission' => 'configuration',
    ],

    'system.broadcast_unreachable' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => BroadcastUnreachable::class,
        'permission' => 'configuration',
    ],

    'system.exchange_rates_stale' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => ExchangeRatesStale::class,
        'permission' => 'configuration',
    ],

    'system.index_incomplete' => [
        'audience' => AdminNotifications::AUDIENCE,
        'group' => 'system',
        'class' => IndexIncomplete::class,
        'permission' => 'configuration',
    ],
];
