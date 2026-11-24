<?php

namespace EcomForYou\Tenancy\Tests;

use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenancyTestCase extends TestCase
{
    /**
     * Whether this process has run the landlord migrations yet.
     */
    protected static bool $landlordMigrated = false;

    /**
     * The connections the test transaction runs on, fixed at its start so a switch to the tenant connection never moves it.
     *
     * @var list<string>|null
     */
    protected ?array $transactedConnections = null;

    /**
     * Create a tenant on the landlord connection, active and with its own subdomain unless told otherwise.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createTenant(array $attributes = []): Tenant
    {
        return app(TenantRepository::class)->create(array_merge([
            'name' => 'Test Store',
            'subdomain' => 'store-'.Str::lower(Str::random(10)),
            'status' => TenantStatus::ACTIVE,
            'owner_email' => 'owner@example.com',
        ], $attributes));
    }

    /**
     * Serve the landlord connection from the store test database, and migrate its tables there before the test transaction opens.
     */
    protected function setUpTraits()
    {
        $this->useStoreDatabaseAsLandlord();

        return parent::setUpTraits();
    }

    /**
     * The connections the test transaction runs on: the store's default connection as it was when the test began.
     *
     * @return list<string>
     */
    protected function connectionsToTransact(): array
    {
        return $this->transactedConnections ??= [config('database.default')];
    }

    /**
     * Resolve the landlord connection to the very connection the test transaction runs on, so landlord rows roll back with it.
     */
    protected function useStoreDatabaseAsLandlord(): void
    {
        $database = $this->app['db'];

        $store = config('database.default');

        $database->extend(config('tenancy.landlord_connection'), fn () => $database->connection($store));

        if (! self::$landlordMigrated) {
            Artisan::call('landlord:migrate', ['--force' => true, '--no-interaction' => true]);

            self::$landlordMigrated = true;
        }
    }
}
