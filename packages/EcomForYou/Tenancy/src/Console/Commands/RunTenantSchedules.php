<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Jobs\RunTenantSchedule;
use EcomForYou\Tenancy\Scheduling\TenantScheduleRunner;
use Illuminate\Console\Command;
use InvalidArgumentException;

class RunTenantSchedules extends Command
{
    use SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:schedule-run
        {--tenant=* : Run only these stores\' tasks, by id or subdomain}
        {--queue : Queue one job per store instead of running them here in turn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the scheduled tasks due for every active store; cron runs it every minute in place of schedule:run';

    /**
     * Run, or queue, the due tasks of each active store.
     */
    public function handle(TenantScheduleRunner $tenantScheduleRunner): int
    {
        try {
            $tenants = $this->selectedTenants()
                ->filter(fn ($tenant) => $tenant->status === TenantStatus::ACTIVE)
                ->values();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        if (
            $this->option('queue')
            || config('tenancy.scheduler.queue')
        ) {
            foreach ($tenants as $tenant) {
                RunTenantSchedule::dispatch($tenant);
            }

            $this->components->info("Queued the schedule of {$tenants->count()} store(s).");

            return self::SUCCESS;
        }

        $succeeded = $this->forEachTenant($tenants, fn ($tenant) => $tenantScheduleRunner->run($tenant, $this->output));

        return $succeeded ? self::SUCCESS : self::FAILURE;
    }
}
