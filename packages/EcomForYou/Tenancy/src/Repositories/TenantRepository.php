<?php

namespace EcomForYou\Tenancy\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Tenancy\Contracts\Tenant;

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
    public function findBySubdomain(string $subdomain): ?\EcomForYou\Tenancy\Models\Tenant
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
}
