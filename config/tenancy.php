<?php

use EcomForYou\Core\Core;
use EcomForYou\Tenancy\Tasks\CacheTask;
use EcomForYou\Tenancy\Tasks\DatabaseTask;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\Tasks\ResponseCacheTask;
use EcomForYou\Tenancy\Tasks\SearchTask;
use EcomForYou\Tenancy\Tasks\UrlTask;
use EcomForYou\Theme\AdminThemeResolver;
use EcomForYou\Theme\Themes;
use EcomForYou\Theme\Upload\UploadedPage;

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
    | current tenant. A new tenant's database is named from the prefix and its uuid.
    |
    */

    'landlord_connection' => 'landlord',

    'database' => [
        'connection' => 'tenant',

        'template' => env('TENANCY_DATABASE_TEMPLATE'),

        'prefix' => env('TENANCY_DATABASE_PREFIX', 'ecomforyou_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Disks
    |--------------------------------------------------------------------------
    |
    | The disks re-rooted for the current tenant, as paths under `storage/`. A public disk's url is
    | the tenant's own address plus the given path, served by the single `storage:link`. The local
    | root holds the files that cannot go through a disk, such as uploaded theme views, and is what
    | `tenant_storage_path()` resolves under.
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
