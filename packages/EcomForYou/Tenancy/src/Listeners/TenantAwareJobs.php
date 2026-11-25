<?php

namespace EcomForYou\Tenancy\Listeners;

use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Exceptions\TenantNotFoundException;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class TenantAwareJobs
{
    /**
     * The tenant, or none, that was current before each running job made its own tenant current, keyed by job.
     *
     * @var array<string, Tenant|null>
     */
    protected array $previous = [];

    /**
     * Create the listener; it is held as a singleton so a job's start and finish meet the same record.
     */
    public function __construct(protected Container $app) {}

    /**
     * The values every job payload carries: the id of the tenant current when it was dispatched, if any.
     *
     * @return array<string, int>
     */
    public function payload(): array
    {
        $tenant = $this->context()->current();

        return $tenant ? [config('tenancy.queue.payload_key') => $tenant->getKey()] : [];
    }

    /**
     * Make the job's tenant current before it runs, or leave none current for a job dispatched outside any store.
     * A job for a store that is gone or has no database fails rather than run against another.
     */
    public function processing(JobProcessing $event): void
    {
        $context = $this->context();

        $tenantId = $event->job->payload()[config('tenancy.queue.payload_key')] ?? null;

        $tenant = is_null($tenantId) ? null : $this->findTenant((int) $tenantId);

        $this->previous[$this->key($event->job)] = $context->current();

        if ($tenant) {
            $context->makeCurrent($tenant);
        } elseif ($context->check()) {
            $context->forget();
        }
    }

    /**
     * Put back whichever tenant, or none, was current before the job, once it has finished in any way.
     */
    public function finished(JobProcessed|JobFailed|JobExceptionOccurred $event): void
    {
        $key = $this->key($event->job);

        if (! array_key_exists($key, $this->previous)) {
            return;
        }

        $previous = $this->previous[$key];

        unset($this->previous[$key]);

        $context = $this->context();

        if ($previous) {
            $context->makeCurrent($previous);
        } elseif ($context->check()) {
            $context->forget();
        }
    }

    /**
     * The tenant a job was dispatched for, refused unless its store database exists.
     */
    protected function findTenant(int $id): Tenant
    {
        $tenant = $this->app->make(TenantRepository::class)->findByIdOrSubdomain((string) $id);

        if (
            ! $tenant
            || ! in_array($tenant->status, TenantStatus::withDatabase(), true)
        ) {
            throw new TenantNotFoundException("The job's tenant [{$id}] is gone or has no store to run against.");
        }

        return $tenant;
    }

    /**
     * The key a job's record is kept under for as long as it runs.
     */
    protected function key(Job $job): string
    {
        return $job->uuid() ?? spl_object_hash($job);
    }

    /**
     * The tenant context.
     */
    protected function context(): TenantContext
    {
        return $this->app->make(TenantContext::class);
    }
}
