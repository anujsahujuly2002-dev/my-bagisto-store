<?php

use Illuminate\Support\Facades\Process;

/**
 * Boot the application in a process of its own with tenancy on, serve the given urls, and return what it reports.
 */
function bootWithoutTenant(array $urls = []): array
{
    $connection = config('database.connections.'.config('database.default'));

    $result = Process::path(base_path())
        ->env([
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => (string) ($connection['host'] ?? ''),
            'DB_PORT' => (string) ($connection['port'] ?? ''),
            'DB_DATABASE' => (string) $connection['database'],
            'DB_USERNAME' => (string) ($connection['username'] ?? ''),
            'DB_PASSWORD' => (string) ($connection['password'] ?? ''),
            'DB_LANDLORD_DATABASE' => (string) $connection['database'],
            'TENANCY_ENABLED' => 'true',
            'TENANCY_PLATFORM_DOMAIN' => 'platform.test',
            'TENANCY_CENTRAL_HOSTS' => 'platform.test',
            'SESSION_DOMAIN' => '',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
        ])
        ->timeout(60)
        ->run(array_merge([PHP_BINARY, 'packages/EcomForYou/Tenancy/tests/Fixtures/boot-without-tenant.php'], $urls));

    expect($result->successful() && json_validate($result->output()))
        ->toBeTrue(trim($result->output()."\n".$result->errorOutput()));

    return json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);
}

// ============================================================================
// Booting With No Tenant
// ============================================================================

it('should boot with tenancy on without running a single query', function () {
    $report = bootWithoutTenant();

    expect($report['default'])->toBe(config('tenancy.database.connection'))
        ->and($report['booted'])->toBe([]);
});

it('should turn away hosts that name no store without reaching any store', function () {
    $report = bootWithoutTenant([
        'http://no-such-store.platform.test/',
        'http://admin.platform.test/',
        'http://a.nested.platform.test/',
        'http://elsewhere.test/',
    ]);

    $storeQueries = collect($report['queries'])->where('connection', config('tenancy.database.connection'));

    expect($report['statuses'])->each->toBe(404)
        ->and($storeQueries)->toBeEmpty();
});
