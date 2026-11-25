<?php

namespace EcomForYou\Tenancy\Provisioning;

use EcomForYou\Installer\Helpers\DatabaseManager as StoreInstaller;
use EcomForYou\Tenancy\Database\TenantDatabase;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Exceptions\TenantProvisioningException;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisioner
{
    /**
     * The longest failure message kept on the tenant, for the landlord panel to show.
     */
    public const MAX_ERROR_LENGTH = 1000;

    /**
     * Create a new provisioner instance.
     */
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected TenantContext $tenantContext,
        protected TenantDatabase $tenantDatabase,
        protected StoreInstaller $storeInstaller,
        protected FilesystemTask $filesystemTask,
        protected Filesystem $files
    ) {}

    /**
     * Build a store for the tenant: its database, schema, basic data, first admin and folders, then open it. On any
     * failure the half-built store is removed, the tenant is marked failed with the reason, and the error is rethrown.
     *
     * @param  array{name?: string, email: string, password: string, locale?: string, currency?: string, timezone?: string}  $owner
     */
    public function provision(Tenant $tenant, array $owner): Tenant
    {
        if (! $this->tenantRepository->claimForProvisioning($tenant)) {
            throw new TenantProvisioningException("Tenant [{$tenant->subdomain}] is {$tenant->status} and cannot be provisioned.");
        }

        Event::dispatch('tenancy.tenant.provision.before', $tenant);

        $created = false;

        try {
            if ($this->tenantDatabase->exists($tenant->database)) {
                throw new TenantProvisioningException("The database [{$tenant->database}] already exists and is left untouched.");
            }

            $this->tenantDatabase->create($tenant->database);

            $created = true;

            $this->storeInstaller->migrate($this->tenantDatabase->buildConnection($tenant->database));

            $this->tenantDatabase->disconnect();

            $this->tenantContext->run($tenant, fn () => $this->seed($owner));

            $this->createDirectories($tenant);
        } catch (Throwable $exception) {
            $this->fail($tenant, $exception, $created);

            throw $exception;
        }

        $this->tenantRepository->setStatus($tenant, TenantStatus::ACTIVE, [
            'provisioning_error' => null,
            'provisioned_at' => now()->toIso8601String(),
        ]);

        Event::dispatch('tenancy.tenant.provision.after', $tenant);

        return $tenant;
    }

    /**
     * Seed the current store's basic data in the owner's locale, currency and timezone, and create its first admin.
     *
     * @param  array<string, string>  $owner
     */
    protected function seed(array $owner): void
    {
        $locale = $owner['locale'] ?? config('app.locale');

        $currency = $owner['currency'] ?? config('app.currency');

        $this->storeInstaller->runSeeders([
            'default_locale' => $locale,
            'allowed_locales' => [$locale],
            'default_currency' => $currency,
            'allowed_currencies' => [$currency],
            'timezone' => $owner['timezone'] ?? config('app.timezone'),
            'skip_admin_creation' => true,
        ]);

        $this->storeInstaller->insertAdminUser([
            'name' => $owner['name'] ?? StoreInstaller::DEFAULT_ADMIN_NAME,
            'email' => $owner['email'],
            'password' => $owner['password'],
        ]);
    }

    /**
     * Create the tenant's own folders: its local root and the root of each tenant disk.
     */
    protected function createDirectories(Tenant $tenant): void
    {
        foreach ($this->filesystemTask->directories($tenant) as $directory) {
            $this->files->ensureDirectoryExists($directory);
        }
    }

    /**
     * Remove what this run built, a database only when this run created it, and mark the tenant failed with the reason.
     */
    protected function fail(Tenant $tenant, Throwable $exception, bool $databaseCreated): void
    {
        Log::error('Provisioning tenant ['.$tenant->subdomain.'] failed: '.$exception->getMessage(), [
            'tenant' => $tenant->getKey(),
            'exception' => $exception,
        ]);

        $this->cleanUp(fn () => $this->tenantDatabase->disconnect(), $tenant);

        if ($databaseCreated) {
            $this->cleanUp(fn () => $this->tenantDatabase->drop($tenant->database), $tenant);
        }

        foreach ($this->filesystemTask->directories($tenant) as $directory) {
            $this->cleanUp(fn () => $this->files->deleteDirectory($directory), $tenant);
        }

        $this->tenantRepository->setStatus($tenant, TenantStatus::FAILED, [
            'provisioning_error' => Str::limit($exception->getMessage(), self::MAX_ERROR_LENGTH),
        ]);
    }

    /**
     * Run one clean-up step, logging rather than throwing when it fails so the remaining steps still run.
     */
    protected function cleanUp(callable $step, Tenant $tenant): void
    {
        try {
            $step();
        } catch (Throwable $exception) {
            Log::error('Cleaning up tenant ['.$tenant->subdomain.'] after a failed provisioning failed: '.$exception->getMessage(), [
                'tenant' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
