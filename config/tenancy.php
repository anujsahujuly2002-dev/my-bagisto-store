<?php

use EcomForYou\Core\Core;
use EcomForYou\Product\Services\Search\SearchEngineManager;
use EcomForYou\Tenancy\Tasks\CacheTask;
use EcomForYou\Tenancy\Tasks\DatabaseTask;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\Tasks\ResponseCacheTask;
use EcomForYou\Tenancy\Tasks\SearchTask;
use EcomForYou\Tenancy\Tasks\UrlTask;
use EcomForYou\Theme\AdminThemeResolver;
use EcomForYou\Theme\Themes;
use EcomForYou\Theme\Upload\UploadedPage;
use Illuminate\Console\Scheduling\Schedule;

$platformDomain = strtolower((string) env('TENANCY_PLATFORM_DOMAIN', 'platform.com'));

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Switch
    |--------------------------------------------------------------------------
    |
    | Off, the application runs as the single store it always was: no request is identified and no
    | connection is switched. On, every store request is identified by its subdomain, and nothing
    | can reach a store's database, cache or files until its tenant is current.
    |
    */

    'enabled' => (bool) env('TENANCY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Platform Domain And Central Hosts
    |--------------------------------------------------------------------------
    |
    | Every store lives at `{subdomain}.{platform_domain}`. The central hosts serve the marketing
    | site and the landlord panel, and skip identification.
    |
    */

    'platform_domain' => $platformDomain,

    'central_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', strtolower((string) env('TENANCY_CENTRAL_HOSTS', $platformDomain.',www.'.$platformDomain)))
    ))),

    'scheme' => env('TENANCY_SCHEME', 'https'),

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains no store may take.
    |
    */

    'reserved_subdomains' => [
        'www',
        'admin',
        'platform',
        'api',
        'app',
        'mail',
        'static',
        'cdn',
        'status',
        'help',
        'support',
    ],

    /*
    |--------------------------------------------------------------------------
    | Connections
    |--------------------------------------------------------------------------
    |
    | The landlord connection holds the tenants, plans and usage. The tenant connection is built
    | from the template connection, which defaults to `DB_CONNECTION`, with the database of the
    | current tenant. A new tenant's database is named from the prefix and its uuid. Databases
    | are created and dropped through the server connection, which defaults to a copy of the
    | template; its user must be allowed to `CREATE DATABASE` and `DROP DATABASE`.
    |
    */

    'landlord_connection' => 'landlord',

    'database' => [
        'connection' => 'tenant',

        'template' => env('TENANCY_DATABASE_TEMPLATE'),

        'prefix' => env('TENANCY_DATABASE_PREFIX', 'ecomforyou_'),

        'server_connection' => env('TENANCY_DATABASE_SERVER_CONNECTION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisioning
    |--------------------------------------------------------------------------
    |
    | The queue a new store is provisioned on. Leave the connection unset to use the default queue
    | connection; with tenancy on, the `database` queue is kept in the landlord database.
    |
    */

    'provisioning' => [
        'queue_connection' => env('TENANCY_PROVISIONING_QUEUE_CONNECTION'),

        'queue' => env('TENANCY_PROVISIONING_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queues
    |--------------------------------------------------------------------------
    |
    | With tenancy on, the `database` queue, job batches and failed jobs are kept in the landlord
    | database, so one worker serves every store. Each job carries the id of the store it was
    | dispatched for, under this payload key, and runs with that store current.
    |
    */

    'queue' => [
        'payload_key' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler
    |--------------------------------------------------------------------------
    |
    | With tenancy on, cron runs `tenants:schedule-run` every minute in place of `schedule:run`.
    | Each active store's schedule is built with it current, and every store command runs as
    | `tenants:run "<command>" --tenant=<id>`, so `withoutOverlapping()` holds per store. These
    | commands are platform-wide and never wrapped.
    |
    */

    'scheduler' => [
        'unwrapped_commands' => [
            'tenants:',
            'landlord:',
            'schedule:',
            'queue:',
        ],

        'queue' => (bool) env('TENANCY_SCHEDULER_QUEUE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Disks
    |--------------------------------------------------------------------------
    |
    | The disks re-rooted for the current tenant. A local disk's root is a path under `storage/`,
    | and a public disk's url is the tenant's own address plus the given path, served by the single
    | `storage:link`. A remote disk's root is a key prefix on its one bucket, added after any prefix
    | the disk already has. The local root holds the files that cannot go through a disk, such as
    | uploaded theme views, and is what `tenant_storage_path()` resolves under.
    |
    */

    'filesystem' => [
        'local_root' => 'app/tenants/{id}',

        'disks' => [
            'public' => [
                'root' => 'app/public/tenants/{id}',
                'url' => '/storage/tenants/{id}',
            ],

            'private' => [
                'root' => 'app/private/tenants/{id}',
            ],

            's3' => [
                'root' => 'tenants/{id}',
            ],

            'r2' => [
                'root' => 'tenants/{id}',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Store Instances
    |--------------------------------------------------------------------------
    |
    | Instances that hold one store's state, such as its channel, locale, currency and themes. Each
    | is forgotten whenever a tenant is made current or forgotten, so an Octane worker or a loop
    | over tenants never hands one store another's state.
    |
    */

    'instances' => [
        Core::class,
        Themes::class,
        UploadedPage::class,
        AdminThemeResolver::class,
        Schedule::class,
        SearchEngineManager::class,
        'product.search.connection.elastic',
        'product.search.engine.database',
        'product.search.engine.elastic',
        'product.search.indexer.elastic',
    ],

    /*
    |--------------------------------------------------------------------------
    | Switching Tasks
    |--------------------------------------------------------------------------
    |
    | Run in this order when a tenant becomes current, and in reverse when it is forgotten.
    |
    */

    'tasks' => [
        DatabaseTask::class,
        CacheTask::class,
        FilesystemTask::class,
        SearchTask::class,
        UrlTask::class,
        ResponseCacheTask::class,
    ],

];
