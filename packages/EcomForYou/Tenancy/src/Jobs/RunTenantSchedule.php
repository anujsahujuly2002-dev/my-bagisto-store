<?php

namespace EcomForYou\Tenancy\Jobs;

use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Scheduling\TenantScheduleRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunTenantSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Run once: a run that fails is followed by the next minute's anyway.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create the job for one store's due tasks.
     */
    public function __construct(public Tenant $tenant) {}

    /**
     * Run the store's due tasks.
     */
    public function handle(TenantScheduleRunner $tenantScheduleRunner): void
    {
        $tenantScheduleRunner->run($this->tenant);
    }
}
