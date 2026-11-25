<?php

namespace EcomForYou\Tenancy\Console\Commands;

use EcomForYou\Tenancy\Console\Commands\Concerns\SelectsTenants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;

class RunForTenants extends Command
{
    use SelectsTenants;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:run
        {artisan : The artisan command to run, with its arguments, quoted as one, such as "indexer:index --mode=full"}
        {--tenant=* : Run only for these tenants, by id or subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run an artisan command once per tenant, with that tenant current';

    /**
     * Run the command for each tenant, carrying on past one that fails and failing the run if any did.
     */
    public function handle(): int
    {
        $command = trim($this->argument('artisan'));

        if (str_starts_with($command, 'tenants:')) {
            $this->components->error('A tenants: command cannot be run per tenant.');

            return self::FAILURE;
        }

        try {
            $tenants = $this->selectedTenants();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $succeeded = $this->forEachTenant($tenants, fn () => Artisan::call($command, [], $this->output));

        return $succeeded ? self::SUCCESS : self::FAILURE;
    }
}
