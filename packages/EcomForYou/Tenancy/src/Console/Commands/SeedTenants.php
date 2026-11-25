<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

class SeedTenants extends Command
{
    use ConfirmableTrait, SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed
        {--class= : The seeder class to run, required because the store seeder wipes a store\'s basic data}
        {--tenant=* : Seed only these tenants, by id or subdomain}
        {--force : Seed in production without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a seeder on every tenant database, or the named ones';

    /**
     * Run the seeder for each tenant, carrying on past one that fails and failing the run if any did.
     */
    public function handle(): int
    {
        if (blank($class = $this->option('class'))) {
            $this->components->error('Name the seeder to run with --class.');

            return self::FAILURE;
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        try {
            $tenants = $this->selectedTenants();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $succeeded = $this->forEachTenant($tenants, fn () => Artisan::call('db:seed', [
            '--class' => $class,
            '--force' => true,
            '--no-interaction' => true,
        ], $this->output));

        return $succeeded ? self::SUCCESS : self::FAILURE;
    }
}
