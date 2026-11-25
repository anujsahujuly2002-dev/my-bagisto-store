<?php

namespace EcomForYou\Tenancy\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tenancy\Contracts\Tenant;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Models\Tenant as TenantModel;
use Illuminate\Support\Collection;

class TenantRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Tenant::class;
    }

    /**
     * Find the tenant that owns a subdomain, whatever its status.
     */
    public function findBySubdomain(string $subdomain): ?TenantModel
    {
        return $this->model
            ->newQuery()
            ->where('subdomain', strtolower(trim($subdomain)))
            ->first();
    }

    /**
     * Whether a subdomain is taken by any tenant other than the one given.
     */
    public function subdomainTaken(string $subdomain, ?int $ignoreId = null): bool
    {
        return $this->model
            ->newQuery()
            ->where('subdomain', strtolower(trim($subdomain)))
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Find a tenant by its id, or by its subdomain when the key is not a number.
     */
    public function findByIdOrSubdomain(string $key): ?TenantModel
    {
        if (ctype_digit($key)) {
            return $this->model->newQuery()->find((int) $key);
        }

        return $this->findBySubdomain($key);
    }

    /**
     * Every tenant whose database has been built, oldest first.
     *
     * @return Collection<int, TenantModel>
     */
    public function withDatabase(): Collection
    {
        return $this->model
            ->newQuery()
            ->whereIn('status', TenantStatus::withDatabase())
            ->orderBy('id')
            ->get();
    }

    /**
     * Move a tenant that can be provisioned to provisioning in one statement, so two runs never build the same
     * store. Whether this run claimed it.
     */
    public function claimForProvisioning(TenantModel $tenant): bool
    {
        $claimed = $this->model
            ->newQuery()
            ->whereKey($tenant->getKey())
            ->whereIn('status', TenantStatus::provisionable())
            ->update(['status' => TenantStatus::PROVISIONING]) === 1;

        $tenant->refresh();

        return $claimed;
    }

    /**
     * Move a tenant to a status, merging the given values into its data, and save it.
     *
     * @param  array<string, mixed>  $data
     */
    public function setStatus(TenantModel $tenant, string $status, array $data = []): TenantModel
    {
        $tenant->status = $status;

        $tenant->data = array_merge($tenant->data ?? [], $data);

        $tenant->save();

        return $tenant;
    }
}
