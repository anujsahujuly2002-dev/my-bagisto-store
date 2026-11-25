<?php

namespace EcomForYou\Tenancy\Jobs;

use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Provisioning\TenantProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTenant implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Run once: a failed run has already cleaned up and marked the tenant failed, and only a person retries it.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Seconds the build may take, enough for the full schema and seeders on a slow server.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * Create the job on the provisioning queue. The payload is encrypted, since it carries the owner's password.
     *
     * @param  array{name?: string, email: string, password: string, locale?: string, currency?: string, timezone?: string}  $owner
     */
    public function __construct(
        public Tenant $tenant,
        public array $owner
    ) {
        $this->onConnection(config('tenancy.provisioning.queue_connection'));

        $this->onQueue(config('tenancy.provisioning.queue'));
    }

    /**
     * Provision the tenant's store.
     */
    public function handle(TenantProvisioner $tenantProvisioner): void
    {
        $tenantProvisioner->provision($this->tenant, $this->owner);
    }
}
