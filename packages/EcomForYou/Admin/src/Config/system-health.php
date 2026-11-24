<?php

use EcomForYou\Admin\SystemHealth\Checks\ApplicationUrlCheck;
use EcomForYou\Admin\SystemHealth\Checks\BroadcastingCheck;
use EcomForYou\Admin\SystemHealth\Checks\DebugModeCheck;
use EcomForYou\Admin\SystemHealth\Checks\ExchangeRatesCheck;
use EcomForYou\Admin\SystemHealth\Checks\FailedJobsCheck;
use EcomForYou\Admin\SystemHealth\Checks\MailConfigurationCheck;
use EcomForYou\Admin\SystemHealth\Checks\ProductIndexCheck;
use EcomForYou\Admin\SystemHealth\Checks\QueueWorkerCheck;
use EcomForYou\Admin\SystemHealth\Checks\RemoteStorageCheck;
use EcomForYou\Admin\SystemHealth\Checks\SchedulerCheck;
use EcomForYou\Admin\SystemHealth\Checks\SearchEngineCheck;

return [
    /*
    |--------------------------------------------------------------------------
    | Inspection Interval
    |--------------------------------------------------------------------------
    |
    | The fewest seconds between two inspections. Admin requests trigger them,
    | so a stopped queue worker or scheduler is still reported.
    |
    */

    'interval' => 300,

    /*
    |--------------------------------------------------------------------------
    | Checks
    |--------------------------------------------------------------------------
    |
    | The checks an inspection runs, keyed by the `notification_types` key
    | raised while the check fails. Each class extends
    | EcomForYou\Admin\SystemHealth\Checks\AbstractCheck. Packages merge their own
    | into `system_health.checks` from their service provider.
    |
    */

    'checks' => [
        'system.queue_worker_stopped' => QueueWorkerCheck::class,
        'system.failed_jobs' => FailedJobsCheck::class,
        'system.scheduler_stopped' => SchedulerCheck::class,
        'system.mail_not_configured' => MailConfigurationCheck::class,
        'system.debug_mode_enabled' => DebugModeCheck::class,
        'system.app_url_mismatch' => ApplicationUrlCheck::class,
        'system.search_unreachable' => SearchEngineCheck::class,
        'system.storage_unreachable' => RemoteStorageCheck::class,
        'system.broadcast_unreachable' => BroadcastingCheck::class,
        'system.exchange_rates_stale' => ExchangeRatesCheck::class,
        'system.index_incomplete' => ProductIndexCheck::class,
    ],
];
