<?php

use EcomForYou\Installer\Helpers\DatabaseManager as StoreInstaller;
use EcomForYou\Tenancy\Database\TenantDatabase;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Exceptions\TenantProvisioningException;
use EcomForYou\Tenancy\Jobs\ProvisionTenant;
use EcomForYou\Tenancy\Provisioning\TenantProvisioner;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->storageRoot = 'framework/testing/tenancy-'.Str::lower(Str::random(10));

    config([
        'tenancy.enabled' => true,
        'tenancy.database.prefix' => 'eft_test_',
        'tenancy.filesystem.local_root' => $this->storageRoot.'/local/{id}',
        'tenancy.filesystem.disks.public.root' => $this->storageRoot.'/public/{id}',
        'tenancy.filesystem.disks.private.root' => $this->storageRoot.'/private/{id}',
    ]);

    $this->databases = [];
});

afterEach(function () {
    app(TenantContext::class)->forget();

    config(['tenancy.enabled' => false]);

    app(TenantContext::class)->forget();

    foreach ($this->databases as $database) {
        app(TenantDatabase::class)->drop($database);
    }

    File::deleteDirectory(storage_path($this->storageRoot));
});

/**
 * A subdomain no other test, in this run or a parallel one, will take.
 */
function uniqueSubdomain(): string
{
    return 'shop-'.Str::lower(Str::random(12));
}

// ============================================================================
// Provisioning A Store End To End
// ============================================================================

it('should build, migrate and delete a store from the command line', function () {
    $subdomain = uniqueSubdomain();

    $this->artisan('tenants:create', [
        'name' => 'Acme',
        'subdomain' => $subdomain,
        'email' => 'owner@acme.test',
        '--admin-name' => 'Ada Owner',
        '--password' => 'secret-123',
        '--locale' => 'en',
        '--currency' => 'USD',
        '--timezone' => 'Asia/Kolkata',
        '--no-interaction' => true,
    ])->assertExitCode(0);

    $tenant = app(TenantRepository::class)->findBySubdomain($subdomain);

    $this->databases[] = $tenant->database;

    $store = app(TenantContext::class)->run($tenant, fn () => [
        'admin' => DB::table('admins')->first(),
        'channel' => DB::table('channels')->first(),
        'locale' => DB::table('channel_locales')->join('locales', 'locales.id', '=', 'channel_locales.locale_id')->value('locales.code'),
    ]);

    expect($tenant->status)->toBe(TenantStatus::ACTIVE)
        ->and($tenant->data)->toMatchArray(['locale' => 'en', 'currency' => 'USD', 'timezone' => 'Asia/Kolkata', 'provisioning_error' => null])
        ->and(app(TenantDatabase::class)->exists($tenant->database))->toBeTrue()
        ->and($store['admin']->name)->toBe('Ada Owner')
        ->and($store['admin']->email)->toBe('owner@acme.test')
        ->and(Hash::check('secret-123', $store['admin']->password))->toBeTrue()
        ->and($store['channel']->hostname)->toBe($tenant->url())
        ->and($store['channel']->timezone)->toBe('Asia/Kolkata')
        ->and($store['locale'])->toBe('en');

    foreach (app(FilesystemTask::class)->directories($tenant) as $directory) {
        expect(File::isDirectory($directory))->toBeTrue();
    }

    $this->artisan('tenants:migrate', ['--tenant' => [$subdomain], '--force' => true])
        ->expectsOutputToContain('Nothing to migrate')
        ->assertExitCode(0);

    $this->artisan('tenants:delete', ['tenant' => $subdomain, '--force' => true])->assertExitCode(0);

    expect(app(TenantDatabase::class)->exists($tenant->database))->toBeFalse()
        ->and(app(TenantRepository::class)->findBySubdomain($subdomain))->toBeNull();

    foreach (app(FilesystemTask::class)->directories($tenant) as $directory) {
        expect(File::exists($directory))->toBeFalse();
    }
});

// ============================================================================
// Failures
// ============================================================================

it('should drop the database, remove the folders and mark the tenant failed when a step fails', function () {
    $this->mock(StoreInstaller::class, fn ($mock) => $mock->shouldReceive('migrate')->andThrow(new RuntimeException('Migration exploded')));

    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => TenantStatus::PENDING_VERIFICATION]);

    $this->databases[] = $tenant->database;

    expect(fn () => app(TenantProvisioner::class)->provision($tenant, ['email' => 'owner@example.com', 'password' => 'secret-123']))
        ->toThrow(RuntimeException::class, 'Migration exploded');

    $tenant->refresh();

    expect($tenant->status)->toBe(TenantStatus::FAILED)
        ->and($tenant->data['provisioning_error'])->toBe('Migration exploded')
        ->and(app(TenantDatabase::class)->exists($tenant->database))->toBeFalse();

    foreach (app(FilesystemTask::class)->directories($tenant) as $directory) {
        expect(File::exists($directory))->toBeFalse();
    }
});

it('should leave a database it did not create untouched', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => TenantStatus::PENDING_VERIFICATION]);

    $this->databases[] = $tenant->database;

    app(TenantDatabase::class)->create($tenant->database);

    expect(fn () => app(TenantProvisioner::class)->provision($tenant, ['email' => 'owner@example.com', 'password' => 'secret-123']))
        ->toThrow(TenantProvisioningException::class, 'already exists');

    expect($tenant->refresh()->status)->toBe(TenantStatus::FAILED)
        ->and(app(TenantDatabase::class)->exists($tenant->database))->toBeTrue();
});

it('should refuse to provision a tenant that is not waiting for it', function (string $status) {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => $status]);

    expect(fn () => app(TenantProvisioner::class)->provision($tenant, ['email' => 'owner@example.com', 'password' => 'secret-123']))
        ->toThrow(TenantProvisioningException::class);

    expect($tenant->refresh()->status)->toBe($status)
        ->and(app(TenantDatabase::class)->exists($tenant->database))->toBeFalse();
})->with([TenantStatus::ACTIVE, TenantStatus::SUSPENDED, TenantStatus::PROVISIONING]);

// ============================================================================
// Creating A Tenant
// ============================================================================

it('should refuse store details that break the rules without recording a tenant', function (array $overrides) {
    $arguments = array_merge([
        'name' => 'Acme',
        'subdomain' => uniqueSubdomain(),
        'email' => 'owner@acme.test',
        '--password' => 'secret-123',
        '--no-interaction' => true,
    ], $overrides);

    $this->artisan('tenants:create', $arguments)->assertExitCode(1);

    expect(app(TenantRepository::class)->findBySubdomain($arguments['subdomain']))->toBeNull();
})->with([
    'reserved subdomain' => [['subdomain' => 'www']],
    'malformed subdomain' => [['subdomain' => '-bad-']],
    'invalid email' => [['email' => 'not-an-email']],
    'unsupported locale' => [['--locale' => 'xx']],
    'unsupported currency' => [['--currency' => 'XXX']],
    'unknown timezone' => [['--timezone' => 'Mars/Olympus']],
    'short password' => [['--password' => '123']],
]);

it('should refuse a subdomain another tenant has taken', function () {
    $taken = $this->createTenant(['subdomain' => uniqueSubdomain()]);

    $this->artisan('tenants:create', [
        'name' => 'Acme',
        'subdomain' => $taken->subdomain,
        'email' => 'owner@acme.test',
        '--password' => 'secret-123',
        '--no-interaction' => true,
    ])->assertExitCode(1);
});

it('should queue provisioning with a generated password when asked to', function () {
    Bus::fake();

    $subdomain = uniqueSubdomain();

    $this->artisan('tenants:create', [
        'name' => 'Acme',
        'subdomain' => $subdomain,
        'email' => 'owner@acme.test',
        '--queue' => true,
        '--no-interaction' => true,
    ])->expectsOutputToContain('Generated password')->assertExitCode(0);

    $tenant = app(TenantRepository::class)->findBySubdomain($subdomain);

    expect($tenant->status)->toBe(TenantStatus::PENDING_VERIFICATION);

    Bus::assertDispatched(ProvisionTenant::class, fn (ProvisionTenant $job) => $job->tenant->getKey() === $tenant->getKey()
        && $job->owner['email'] === 'owner@acme.test'
        && strlen($job->owner['password']) === 16);
});

it('should encrypt the provisioning job and send it to the provisioning queue', function () {
    config(['tenancy.provisioning.queue_connection' => 'sync', 'tenancy.provisioning.queue' => 'provisioning']);

    $job = new ProvisionTenant($this->createTenant(), ['email' => 'owner@example.com', 'password' => 'secret-123']);

    expect($job)->toBeInstanceOf(ShouldBeEncrypted::class)
        ->and($job->connection)->toBe('sync')
        ->and($job->queue)->toBe('provisioning')
        ->and($job->tries)->toBe(1);
});

// ============================================================================
// Suspending And Resuming
// ============================================================================

it('should suspend an active store and resume it', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain()]);

    $this->artisan('tenants:suspend', ['tenant' => $tenant->subdomain])->assertExitCode(0);

    expect($tenant->refresh()->status)->toBe(TenantStatus::SUSPENDED);

    $this->artisan('tenants:suspend', ['tenant' => (string) $tenant->id, '--resume' => true])->assertExitCode(0);

    expect($tenant->refresh()->status)->toBe(TenantStatus::ACTIVE);
});

it('should refuse to suspend a store that is not active', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => TenantStatus::FAILED]);

    $this->artisan('tenants:suspend', ['tenant' => $tenant->subdomain])->assertExitCode(1);

    $this->artisan('tenants:suspend', ['tenant' => 'no-such-store'])->assertExitCode(1);

    expect($tenant->refresh()->status)->toBe(TenantStatus::FAILED);
});

// ============================================================================
// Running Commands Per Tenant
// ============================================================================

it('should run a command once per named tenant with that tenant current', function () {
    Artisan::command('tenancy-test:whoami', function () {
        $this->line('current='.app(TenantContext::class)->current()->subdomain);
    });

    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'database' => DB::connection()->getDatabaseName()]);

    $this->artisan('tenants:run', ['artisan' => 'tenancy-test:whoami', '--tenant' => [$tenant->subdomain, (string) $tenant->id]])
        ->expectsOutputToContain('current='.$tenant->subdomain)
        ->assertExitCode(0);

    expect(app(TenantContext::class)->check())->toBeFalse();
});

it('should refuse to run for a tenant whose store is not built', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => TenantStatus::FAILED]);

    $this->artisan('tenants:run', ['artisan' => 'about', '--tenant' => [$tenant->subdomain]])->assertExitCode(1);
});

it('should refuse to run a tenants command per tenant', function () {
    $this->artisan('tenants:run', ['artisan' => 'tenants:migrate --force'])->assertExitCode(1);
});

it('should require a seeder class before seeding every store', function () {
    $this->artisan('tenants:seed', ['--force' => true])->assertExitCode(1);
});

// ============================================================================
// Deleting A Tenant
// ============================================================================

it('should refuse to delete a tenant without --force when nobody can be asked', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain()]);

    $this->artisan('tenants:delete', ['tenant' => $tenant->subdomain, '--no-interaction' => true])->assertExitCode(1);

    expect(app(TenantRepository::class)->findBySubdomain($tenant->subdomain))->not->toBeNull();
});

it('should refuse to delete a tenant that is being provisioned', function () {
    $tenant = $this->createTenant(['subdomain' => uniqueSubdomain(), 'status' => TenantStatus::PROVISIONING]);

    $this->artisan('tenants:delete', ['tenant' => $tenant->subdomain, '--force' => true])->assertExitCode(1);

    expect(app(TenantRepository::class)->findBySubdomain($tenant->subdomain))->not->toBeNull();
});
