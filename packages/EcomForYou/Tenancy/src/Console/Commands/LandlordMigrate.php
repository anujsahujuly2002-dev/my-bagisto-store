<?php

namespace EcomForYou\Tenancy\Console\Commands;

use Illuminate\Console\Command;

class LandlordMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landlord:migrate {--force : Run the migrations in production without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the landlord migrations, and only those, on the landlord connection';

    /**
     * Run the landlord migrations from their own folder, which no store migration run ever reads.
     */
    public function handle(): int
    {
        return $this->call('migrate', [
            '--database' => config('tenancy.landlord_connection'),
            '--path' => self::migrationsPath(),
            '--realpath' => true,
            '--force' => (bool) $this->option('force'),
        ]);
    }

    /**
     * The folder holding the landlord migrations.
     */
    public static function migrationsPath(): string
    {
        return dirname(__DIR__, 2).'/Database/Migrations/Landlord';
    }
}
