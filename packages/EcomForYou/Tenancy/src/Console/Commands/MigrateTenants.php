<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

class MigrateTenants extends Command
{
    use ConfirmableTrait, SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate
        {--tenant=* : Migrate only these tenants, by id or subdomain}
        {--force : Run the migrations in production without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the pending store migrations on every tenant database, or the named ones';

    /**
     * Migrate each tenant with it current, carrying on past one that fails and failing the run if any did.
     */
    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        try {
            $tenants = $this->selectedTenants();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $succeeded = $this->forEachTenant($tenants, fn () => Artisan::call('migrate', [
            '--force' => true,
            '--no-interaction' => true,
        ], $this->output));

        return $succeeded ? self::SUCCESS : self::FAILURE;
    }
}
