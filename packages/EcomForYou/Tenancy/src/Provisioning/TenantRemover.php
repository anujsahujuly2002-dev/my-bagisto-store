<?php

namespace EcomForYou\Tenancy\Provisioning;

use EcomForYou\Tenancy\Database\TenantDatabase;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Exceptions\TenantProvisioningException;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\Search\TenantSearchIndices;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Throwable;

class TenantRemover
{
    /**
     * Create a new remover instance.
     */
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected TenantContext $tenantContext,
        protected TenantDatabase $tenantDatabase,
        protected FilesystemTask $filesystemTask,
        protected Filesystem $files,
        protected TenantSearchIndices $tenantSearchIndices
    ) {}

    /**
     * Delete a store for good: its search indices, its database, its folders and its tenant record. A store still
     * being provisioned is refused, since its build would carry on into a database that no longer exists.
     */
    public function delete(Tenant $tenant): void
    {
        if ($tenant->status === TenantStatus::PROVISIONING) {
            throw new TenantProvisioningException("Tenant [{$tenant->subdomain}] is being provisioned and cannot be deleted.");
        }

        Event::dispatch('tenancy.tenant.delete.before', $tenant);

        $this->deleteSearchIndices($tenant);

        if ($this->tenantContext->current()?->is($tenant)) {
            $this->tenantContext->forget();
        }

        $this->tenantDatabase->drop($tenant->database);

        foreach ($this->filesystemTask->directories($tenant) as $directory) {
            $this->files->deleteDirectory($directory);
        }

        $this->tenantRepository->delete($tenant->getKey());

        Event::dispatch('tenancy.tenant.delete.after', $tenant);
    }

    /**
     * Delete the store's search indices while its settings can still be read, reporting rather than stopping the
     * deletion when the store or its cluster cannot be reached.
     */
    protected function deleteSearchIndices(Tenant $tenant): void
    {
        if (! in_array($tenant->status, TenantStatus::withDatabase(), true)) {
            return;
        }

        try {
            $this->tenantContext->run($tenant, fn (Tenant $tenant) => $this->tenantSearchIndices->delete($tenant));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
