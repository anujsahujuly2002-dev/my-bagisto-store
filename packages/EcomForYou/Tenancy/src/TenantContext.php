<?php

namespace EcomForYou\Tenancy;

use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Tasks\SwitchingTask;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Event;
use Throwable;

class TenantContext
{
    /**
     * The tenant current now, if any.
     */
    protected ?Tenant $current = null;

    /**
     * The switching tasks, in the order they run when a tenant becomes current.
     *
     * @var list<SwitchingTask>
     */
    protected array $tasks;

    /**
     * Create the context, building its switching tasks while the configuration is still the single store's.
     */
    public function __construct(protected Container $app)
    {
        $this->tasks = array_map(
            fn (string $task) => $this->app->make($task),
            config('tenancy.tasks', [])
        );
    }

    /**
     * The tenant current now, if any.
     */
    public function current(): ?Tenant
    {
        return $this->current;
    }

    /**
     * Whether a tenant is current.
     */
    public function check(): bool
    {
        return $this->current !== null;
    }

    /**
     * Make a tenant current, forgetting the one before it. A task or listener that fails leaves no tenant current.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        if ($this->current?->is($tenant)) {
            return;
        }

        if ($this->current) {
            $this->forget();
        }

        try {
            foreach ($this->tasks as $task) {
                $task->makeCurrent($tenant);
            }
        } catch (Throwable $exception) {
            $this->forgetTasks();

            throw $exception;
        }

        $this->current = $tenant;

        try {
            Event::dispatch(new TenantMadeCurrent($tenant));
        } catch (Throwable $exception) {
            $this->forget();

            throw $exception;
        }
    }

    /**
     * Leave no tenant current.
     */
    public function forget(): void
    {
        $previous = $this->current;

        $this->forgetTasks();

        $this->current = null;

        if ($previous) {
            Event::dispatch(new TenantForgotten($previous));
        }
    }

    /**
     * Run a callback with a tenant current, then put back whichever tenant, or none, was current before.
     *
     * @template TResult
     *
     * @param  callable(Tenant): TResult  $callback
     * @return TResult
     */
    public function run(Tenant $tenant, callable $callback): mixed
    {
        $previous = $this->current;

        $this->makeCurrent($tenant);

        try {
            return $callback($tenant);
        } finally {
            if ($previous) {
                $this->makeCurrent($previous);
            } else {
                $this->forget();
            }
        }
    }

    /**
     * Run every task's forget step, last task first.
     */
    protected function forgetTasks(): void
    {
        foreach (array_reverse($this->tasks) as $task) {
            $task->forgetCurrent();
        }
    }
}
