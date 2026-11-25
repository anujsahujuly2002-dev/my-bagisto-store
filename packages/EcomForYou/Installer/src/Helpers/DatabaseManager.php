<?php

namespace EcomForYou\Installer\Helpers;

use EcomForYou\Core\Concerns\SyncsPostgresSequences;
use EcomForYou\Installer\Database\Seeders\DatabaseSeeder as BagistoDatabaseSeeder;
use EcomForYou\Installer\Database\Seeders\ProductTableSeeder;
use EcomForYou\Product\Console\Commands\Indexer;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseManager
{
    use SyncsPostgresSequences;

    /**
     * Admin User ID for which the admin user will be created during installation.
     */
    const int USER_ID = 1;

    /**
     * Default admin name for which the admin user will be created during installation.
     */
    const string DEFAULT_ADMIN_NAME = 'Admin';

    /**
     * Default admin email for which the admin user will be created during installation.
     */
    const string DEFAULT_ADMIN_EMAIL = 'admin@example.com';

    /**
     * Default admin password for which the admin user will be created during installation.
     */
    const string DEFAULT_ADMIN_PASSWORD = 'admin123';

    /**
     * Check if the application is installed.
     */
    public function isInstalled(): bool
    {
        if (! file_exists(base_path('.env'))) {
            return false;
        }

        try {
            DB::connection()->getPDO();

            $isConnected = (bool) DB::connection()->getDatabaseName();

            if (! $isConnected) {
                return false;
            }

            $hasTable = Schema::hasTable('admins');

            if (! $hasTable) {
                return false;
            }

            $userCount = DB::table('admins')->count();

            if (! $userCount) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check database connection.
     */
    public function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Drop all the tables and migrate in the database.
     */
    public function migrateFresh(): bool
    {
        try {
            Artisan::call('migrate:fresh');

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Run every pending migration on the given connection, or the default one, throwing when the run fails.
     * The installer and tenant provisioning both migrate a store through this one step.
     */
    public function migrate(?string $connection = null): void
    {
        $exitCode = Artisan::call('migrate', array_filter([
            '--database' => $connection,
            '--force' => true,
            '--no-interaction' => true,
        ], fn ($value) => ! is_null($value)));

        if ($exitCode !== 0) {
            throw new RuntimeException('The migrations failed: '.trim(Artisan::output()));
        }
    }

    /**
     * Seed a store's basic data on the default connection, throwing when a seeder fails. The installer and
     * tenant provisioning both seed a store through this one step.
     */
    public function runSeeders(array $parameters = []): void
    {
        app(BagistoDatabaseSeeder::class)->run($parameters);
    }

    /**
     * Seed the database.
     */
    public function seed($parameter): bool
    {
        try {
            $this->runSeeders($parameter);

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Generate fake product data.
     *
     * @return void|string
     */
    public function seedSampleProducts($parameters): bool
    {
        try {
            app(ProductTableSeeder::class)->run($parameters);

            Artisan::registerCommand(app(Indexer::class));

            Artisan::call('indexer:index', [
                '--mode' => ['full'],
                '--quiet' => true,
            ]);

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Create the store's first admin on the default connection, throwing when it cannot be created. The installer
     * and tenant provisioning both create the first admin through this one step.
     */
    public function insertAdminUser(array $data = []): void
    {
        DB::table('admins')->insert([
            'id' => self::USER_ID,
            'name' => $data['name'] ?? self::DEFAULT_ADMIN_NAME,
            'email' => $data['email'] ?? self::DEFAULT_ADMIN_EMAIL,
            'password' => bcrypt($data['password'] ?? self::DEFAULT_ADMIN_PASSWORD),
            'role_id' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncPostgresSequences(['admins']);
    }

    /**
     * Create admin user.
     */
    public function createAdminUser(array $data = []): bool
    {
        try {
            $this->insertAdminUser($data);

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }
}
