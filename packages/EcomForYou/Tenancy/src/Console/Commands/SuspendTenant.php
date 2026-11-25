<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SuspendTenant extends Command
{
    use SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:suspend
        {tenant : The tenant to suspend or resume, by id or subdomain}
        {--resume : Open a suspended store again instead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close an active store, keeping its data, or open a suspended one again with --resume';

    /**
     * Move the tenant between active and suspended.
     */
    public function handle(TenantRepository $tenantRepository): int
    {
        try {
            $tenant = $this->findTenant($this->argument('tenant'));
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        [$from, $to] = $this->option('resume')
            ? [TenantStatus::SUSPENDED, TenantStatus::ACTIVE]
            : [TenantStatus::ACTIVE, TenantStatus::SUSPENDED];

        if ($tenant->status !== $from) {
            $this->components->error("Tenant [{$tenant->subdomain}] is {$tenant->status}, not {$from}.");

            return self::FAILURE;
        }

        $tenantRepository->setStatus($tenant, $to);

        $this->components->info("Tenant [{$tenant->subdomain}] is {$to}.");

        return self::SUCCESS;
    }
}
