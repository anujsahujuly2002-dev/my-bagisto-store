<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Database\DatabaseManager;

class DatabaseTask implements SwitchingTask
{
    /**
     * The default connection the single store used before any switch.
     */
    protected string $originalDefault;

    /**
     * Create the task, remembering the single store's default connection.
     */
    public function __construct(protected DatabaseManager $db)
    {
        $this->originalDefault = config('database.default');
    }

    /**
     * Point the default connection at the tenant's database.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->useDatabase($tenant->database);
    }

    /**
     * Leave the tenant connection with no database while tenancy is on, or give the single store its connection back.
     */
    public function forgetCurrent(): void
    {
        if (config('tenancy.enabled')) {
            $this->useDatabase(null);

            return;
        }

        $this->db->purge($this->connection());

        $this->db->setDefaultConnection($this->originalDefault);
    }

    /**
     * Point the tenant connection at the given database and make it the default. A connection already built keeps
     * its object and gets a fresh PDO, so whatever was handed it at boot, such as a cache store, follows the tenant.
     */
    protected function useDatabase(?string $database): void
    {
        $name = $this->connection();

        $template = config('tenancy.database.template') ?: $this->originalDefault;

        config(["database.connections.{$name}" => array_merge(
            (array) config("database.connections.{$template}"),
            ['database' => $database]
        )]);

        if (array_key_exists($name, $this->db->getConnections())) {
            $this->db->reconnect($name)->setDatabaseName((string) $database);
        }

        $this->db->setDefaultConnection($name);
    }

    /**
     * The name of the tenant connection.
     */
    protected function connection(): string
    {
        return config('tenancy.database.connection');
    }
}
