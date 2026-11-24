<?php

namespace EcomForYou\Tenancy\Http\Middleware;

use Closure;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Seconds a visitor is asked to wait before retrying a store that is being set up.
     */
    public const PROVISIONING_RETRY_AFTER = 30;

    /**
     * Create the middleware; the context and repository are resolved only once tenancy is on.
     */
    public function __construct(protected Container $app) {}

    /**
     * Make the store the request's subdomain names current, or answer for it when it cannot be opened.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('tenancy.enabled')) {
            return $next($request);
        }

        $context = $this->app->make(TenantContext::class);

        if ($context->check()) {
            $context->forget();
        }

        $host = strtolower($request->getHost());

        if (in_array($host, config('tenancy.central_hosts', []), true)) {
            return $next($request);
        }

        $tenant = $this->tenantFor($host);

        return match ($tenant?->status) {
            TenantStatus::ACTIVE => $this->open($context, $tenant, $request, $next),
            TenantStatus::SUSPENDED => $this->unavailable('suspended', 403),
            TenantStatus::PROVISIONING => $this->unavailable('provisioning', 503, ['Retry-After' => self::PROVISIONING_RETRY_AFTER]),
            default => $this->unavailable('not-found', 404),
        };
    }

    /**
     * The tenant a host names: a single label directly under the platform domain, never a reserved one.
     */
    protected function tenantFor(string $host): ?Tenant
    {
        $suffix = '.'.config('tenancy.platform_domain');

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($suffix));

        if (
            $subdomain === ''
            || str_contains($subdomain, '.')
            || in_array($subdomain, config('tenancy.reserved_subdomains', []), true)
        ) {
            return null;
        }

        return $this->app->make(TenantRepository::class)->findBySubdomain($subdomain);
    }

    /**
     * Make the tenant current and hand the request on.
     */
    protected function open(TenantContext $context, Tenant $tenant, Request $request, Closure $next): Response
    {
        $context->makeCurrent($tenant);

        return $next($request);
    }

    /**
     * A page saying why the store cannot be opened, rendered without touching any store's data.
     *
     * @param  array<string, mixed>  $headers
     */
    protected function unavailable(string $reason, int $status, array $headers = []): Response
    {
        return response()->view('tenancy::errors.unavailable', ['reason' => $reason], $status, $headers);
    }
}
