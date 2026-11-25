<?php

namespace EcomForYou\Tenancy\Scheduling;

use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Console\Output\OutputInterface;

class TenantScheduleRunner
{
    /**
     * Create a new runner instance.
     */
    public function __construct(
        protected Container $app,
        protected TenantContext $tenantContext
    ) {}

    /**
     * Run the tasks due for a store: its schedule is built with it current, so a task timed from the store's own
     * settings follows them, and each store command is sent to a process that makes the store current first.
     */
    public function run(Tenant $tenant, ?OutputInterface $output = null): int
    {
        return $this->tenantContext->run($tenant, function (Tenant $tenant) use ($output) {
            $this->wrap($this->app->make(Schedule::class), $tenant);

            return Artisan::call('schedule:run', ['--no-interaction' => true], $output);
        });
    }

    /**
     * Rewrite each artisan task of a schedule as `tenants:run "<command>" --tenant=<id>`, leaving closures, which
     * run in this process with the store current, and the platform-wide commands as they are.
     */
    public function wrap(Schedule $schedule, Tenant $tenant): Schedule
    {
        $artisan = Application::formatCommandString('');

        foreach ($schedule->events() as $event) {
            if (
                $event instanceof CallbackEvent
                || ! str_starts_with((string) $event->command, $artisan)
            ) {
                continue;
            }

            $command = trim(substr($event->command, strlen($artisan)));

            if ($this->isUnwrapped($command)) {
                continue;
            }

            $event->command = Application::formatCommandString('tenants:run')
                .' '.ProcessUtils::escapeArgument($command)
                .' --tenant='.$tenant->getKey();
        }

        return $schedule;
    }

    /**
     * Whether a command is platform-wide and runs as scheduled.
     */
    protected function isUnwrapped(string $command): bool
    {
        foreach (config('tenancy.scheduler.unwrapped_commands', []) as $prefix) {
            if (str_starts_with($command, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
