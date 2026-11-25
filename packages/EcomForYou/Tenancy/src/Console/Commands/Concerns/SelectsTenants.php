<?php

namespace EcomForYou\Tenancy\Console\Commands\Concerns;

use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

trait SelectsTenants
{
    /**
     * The tenants named by `--tenant`, by id or subdomain, or every tenant whose database is built when none is named.
     *
     * @return Collection<int, Tenant>
     */
    protected function selectedTenants(): Collection
    {
        $keys = array_filter((array) $this->option('tenant'), 'filled');

        if (empty($keys)) {
            return app(TenantRepository::class)->withDatabase();
        }

        return collect($keys)->map(function (string $key) {
            $tenant = $this->findTenant($key);

            if (! in_array($tenant->status, TenantStatus::withDatabase(), true)) {
                throw new InvalidArgumentException("Tenant [{$tenant->subdomain}] is {$tenant->status} and has no store to run against.");
            }

            return $tenant;
        })->unique('id')->values();
    }

    /**
     * The tenant a key names, by id or subdomain.
     */
    protected function findTenant(string $key): Tenant
    {
        return app(TenantRepository::class)->findByIdOrSubdomain($key)
            ?? throw new InvalidArgumentException("No tenant [{$key}] exists.");
    }

    /**
     * Run a step once per tenant with that tenant current, carrying on past a failing tenant. Whether every tenant
     * succeeded, a step returning a non-zero exit code counting as a failure.
     *
     * @param  Collection<int, Tenant>  $tenants
     * @param  callable(Tenant): ?int  $step
     */
    protected function forEachTenant(Collection $tenants, callable $step): bool
    {
        if ($tenants->isEmpty()) {
            $this->components->warn('No tenant to run against.');

            return true;
        }

        $failed = [];

        foreach ($tenants as $tenant) {
            $this->components->info("Tenant #{$tenant->id} [{$tenant->subdomain}]");

            try {
                $exitCode = app(TenantContext::class)->run($tenant, $step);
            } catch (Throwable $exception) {
                report($exception);

                $this->components->error($exception->getMessage());

                $exitCode = self::FAILURE;
            }

            if ($exitCode) {
                $failed[] = $tenant->subdomain;
            }
        }

        if ($failed) {
            $this->components->error('Failed for: '.implode(', ', $failed));
        }

        return empty($failed);
    }
}
