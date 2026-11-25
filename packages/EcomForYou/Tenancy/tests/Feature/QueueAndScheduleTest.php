<?php

use EcomForYou\Core\Facades\ElasticSearch;
use EcomForYou\Product\Services\Search\SearchEngineConfigurator;
use EcomForYou\Product\Services\Search\SearchEngineManager;
use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\Jobs\RunTenantSchedule;
use EcomForYou\Tenancy\Providers\TenancyServiceProvider;
use EcomForYou\Tenancy\Repositories\TenantRepository;
use EcomForYou\Tenancy\Scheduling\TenantScheduleRunner;
use EcomForYou\Tenancy\Search\TenantSearchIndices;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $landlord = config('tenancy.landlord_connection');

    config([
        'queue.connections.database.connection' => $landlord,
        'queue.connections.database.queue' => 'tenancy-test',
        'queue.failed.database' => $landlord,
    ]);

    $this->tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);
});

afterEach(function () {
    app(TenantContext::class)->forget();
});

/**
 * The payload of the newest job waiting on the test queue.
 *
 * @return array<string, mixed>
 */
function newestJobPayload(): array
{
    return json_decode(DB::connection(config('tenancy.landlord_connection'))
        ->table('jobs')
        ->where('queue', 'tenancy-test')
        ->latest('id')
        ->value('payload'), true);
}

/**
 * Work the test queue once, as a worker serving every store would.
 */
function workTestQueue(): void
{
    Artisan::call('queue:work', [
        'connection' => 'database',
        '--queue' => 'tenancy-test',
        '--once' => true,
        '--stop-when-empty' => true,
        '--tries' => 1,
    ]);
}

// ============================================================================
// Queued Jobs
// ============================================================================

it('should stamp a job with the tenant it was dispatched for, and no tenant outside any store', function () {
    Queue::connection('database')->push(fn () => null);

    expect(newestJobPayload())->not->toHaveKey('tenant_id');

    app(TenantContext::class)->makeCurrent($this->tenant);

    Queue::connection('database')->push(fn () => null);

    expect(newestJobPayload()['tenant_id'])->toBe($this->tenant->id);
});

it('should run a job with its tenant current and leave the worker with none afterwards', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    Queue::connection('database')->push(function () {
        app()->instance('tenancy-test.seen', app(TenantContext::class)->current()?->subdomain);
    });

    $context->forget();

    workTestQueue();

    expect(app('tenancy-test.seen'))->toBe($this->tenant->subdomain)
        ->and($context->check())->toBeFalse();
});

it('should fail a job whose store is gone rather than run it against another', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    Queue::connection('database')->push(function () {
        app()->instance('tenancy-test.ran', true);
    });

    $context->forget();

    app(TenantRepository::class)->setStatus($this->tenant, TenantStatus::FAILED);

    workTestQueue();

    expect(app()->bound('tenancy-test.ran'))->toBeFalse()
        ->and($context->check())->toBeFalse()
        ->and(DB::connection(config('tenancy.landlord_connection'))->table('failed_jobs')->where('queue', 'tenancy-test')->exists())->toBeTrue();
});

it('should give back the tenant that dispatched a job run on the sync queue', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    dispatch_sync(fn () => null);

    expect($context->current()?->is($this->tenant))->toBeTrue();
});

it('should point the queue, batch and failed job tables at the landlord database', function () {
    config(['queue.connections.database.connection' => null, 'queue.batching.database' => 'mysql', 'queue.failed.database' => 'mysql']);

    (new ReflectionMethod(TenancyServiceProvider::class, 'keepQueueTablesInLandlord'))->invoke(new TenancyServiceProvider(app()));

    expect(config('queue.connections.database.connection'))->toBe('landlord')
        ->and(config('queue.batching.database'))->toBe('landlord')
        ->and(config('queue.failed.database'))->toBe('landlord');
});

// ============================================================================
// Scheduler
// ============================================================================

it('should send each store command to a process that makes the store current first', function () {
    $schedule = new Schedule;

    $schedule->command('invoice:cron')->dailyAt('3:00')->withoutOverlapping();

    $schedule->command('indexer:index --type=price');

    $schedule->command('tenants:migrate');

    $schedule->exec('ls');

    $schedule->call(fn () => null);

    app(TenantScheduleRunner::class)->wrap($schedule, $this->tenant);

    $commands = array_map(fn ($event) => $event->command, $schedule->events());

    expect($commands[0])->toBe(Application::formatCommandString('tenants:run')." 'invoice:cron' --tenant=".$this->tenant->id)
        ->and($commands[1])->toBe(Application::formatCommandString('tenants:run')." 'indexer:index --type=price' --tenant=".$this->tenant->id)
        ->and($commands[2])->toBe(Application::formatCommandString('tenants:migrate'))
        ->and($commands[3])->toBe('ls')
        ->and($commands[4])->toBeNull();
});

it('should keep one store from blocking another with withoutOverlapping', function () {
    $other = $this->createTenant();

    $mutexes = collect([$this->tenant, $other])->map(function ($tenant) {
        $schedule = new Schedule;

        $schedule->command('theme:switch-scheduled')->everyMinute()->withoutOverlapping();

        return app(TenantScheduleRunner::class)->wrap($schedule, $tenant)->events()[0]->mutexName();
    });

    expect($mutexes[0])->not->toBe($mutexes[1]);
});

it('should build each store schedule afresh with that store current', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    $first = app(Schedule::class);

    $context->forget();

    expect(app(Schedule::class))->not->toBe($first);
});

it('should run schedule:run with the store schedule wrapped and the store current', function () {
    $tenant = $this->tenant;

    Artisan::command('schedule:run', function () use ($tenant) {
        app()->instance('tenancy-test.schedule', [
            'tenant' => app(TenantContext::class)->current()?->id,
            'wrapped' => collect(app(Schedule::class)->events())
                ->contains(fn ($event) => str_contains((string) $event->command, "'invoice:cron' --tenant=".$tenant->id)),
        ]);
    });

    app(TenantScheduleRunner::class)->run($this->tenant);

    expect(app('tenancy-test.schedule'))->toBe(['tenant' => $this->tenant->id, 'wrapped' => true])
        ->and(app(TenantContext::class)->check())->toBeFalse();
});

it('should queue one schedule run per active store and none for a suspended one', function () {
    Bus::fake();

    $suspended = $this->createTenant(['status' => TenantStatus::SUSPENDED]);

    $this->artisan('tenants:schedule-run', ['--tenant' => [$this->tenant->subdomain, $suspended->subdomain], '--queue' => true])
        ->assertExitCode(0);

    Bus::assertDispatchedTimes(RunTenantSchedule::class, 1);

    Bus::assertDispatched(RunTenantSchedule::class, fn (RunTenantSchedule $job) => $job->tenant->getKey() === $this->tenant->getKey());
});

// ============================================================================
// Search
// ============================================================================

it('should confine a store to its own index prefix whatever prefix it chose', function (?string $chosen, string $expected) {
    $this->mock(SearchEngineConfigurator::class, function ($mock) use ($chosen) {
        $mock->shouldReceive('configure')->andReturnUsing(function () use ($chosen) {
            if ($chosen !== null) {
                config(['elasticsearch.index_prefix' => $chosen]);
            }
        });
    });

    app()->forgetInstance(TenantContext::class);

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(config('elasticsearch.index_prefix'))->toBe(str_replace('{id}', (string) $this->tenant->id, $expected));
})->with([
    'no prefix chosen' => [null, 't{id}_'],
    'own prefix chosen' => ['shop_', 't{id}_shop_'],
    'another store prefix chosen' => ['t1_', 't{id}_t1_'],
]);

it('should leave the search cluster alone when the store never turned search on', function () {
    ElasticSearch::shouldReceive('indices')->never();

    $this->setConfig(SearchEngineManager::ENABLED_KEY, '0');

    expect(app(TenantSearchIndices::class)->delete($this->tenant))->toBe([]);
});

it('should never delete search indices for a store with no prefix of its own', function () {
    ElasticSearch::shouldReceive('indices')->never();

    $this->setConfig(SearchEngineManager::ENABLED_KEY, '1');

    $this->tenant->data = ['index_prefix' => ''];

    expect(app(TenantSearchIndices::class)->delete($this->tenant))->toBe([]);
});

it('should delete only the indices under the store prefix by name', function () {
    $this->setConfig(SearchEngineManager::ENABLED_KEY, '1');

    $prefix = 't'.$this->tenant->id.'_';

    $indices = Mockery::mock();

    $indices->shouldReceive('get')
        ->with(Mockery::on(fn ($params) => $params['index'] === $prefix.'*'))
        ->andReturn(Mockery::mock(['asArray' => [
            $prefix.'products_default_en_index' => [],
            't'.$this->tenant->id.'0_products_default_en_index' => [],
        ]]));

    $indices->shouldReceive('delete')->once()->with(['index' => $prefix.'products_default_en_index']);

    ElasticSearch::shouldReceive('indices')->andReturn($indices);

    expect(app(TenantSearchIndices::class)->delete($this->tenant))->toBe([$prefix.'products_default_en_index']);
});
