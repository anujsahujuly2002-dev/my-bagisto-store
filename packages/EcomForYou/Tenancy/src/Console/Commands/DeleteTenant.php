<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use EcomForYou\Tenancy\Provisioning\TenantRemover;
use Illuminate\Console\Command;
use Throwable;

class DeleteTenant extends Command
{
    use SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:delete
        {tenant : The tenant to delete, by id or subdomain}
        {--force : Delete without asking, which a run without interaction is otherwise refused without}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a tenant for good: its database, its files and its record';

    /**
     * Confirm, then drop the tenant's database and folders and remove its record.
     */
    public function handle(TenantRemover $tenantRemover): int
    {
        try {
            $tenant = $this->findTenant($this->argument('tenant'));
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            if (! $this->input->isInteractive()) {
                $this->components->error('Deleting a tenant needs --force when run without interaction.');

                return self::FAILURE;
            }

            if (! $this->components->confirm("Permanently delete [{$tenant->subdomain}] with its database [{$tenant->database}] and files?")) {
                return self::FAILURE;
            }
        }

        try {
            $tenantRemover->delete($tenant);
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Tenant [{$tenant->subdomain}] is deleted.");

        return self::SUCCESS;
    }
}
