<?php

namespace EcomForYou\Tenancy\Database;

use EcomForYou\Tenancy\Exceptions\TenancyMisconfiguredException;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

class TenantDatabase
{
    /**
     * The connection databases are created and dropped through, kept apart from every connection in use.
     */
    public const SERVER_CONNECTION = 'tenancy_server';

    /**
     * The connection a database being built is migrated through, before its tenant can be made current.
     */
    public const BUILD_CONNECTION = 'tenancy_build';

    /**
     * The characters a tenant database name may hold, so no name ever needs more than quoting.
     */
    public const NAME_PATTERN = '/^[A-Za-z0-9_]{1,63}$/';

    /**
     * Create a new tenant database helper.
     */
    public function __construct(protected DatabaseManager $db) {}

    /**
     * Whether a database of this name exists on the server.
     */
    public function exists(string $name): bool
    {
        $connection = $this->server();

        $query = $connection->getDriverName() === 'pgsql'
            ? 'select datname from pg_database where datname = ?'
            : 'select schema_name from information_schema.schemata where schema_name = ?';

        return $connection->selectOne($query, [$this->validName($name)]) !== null;
    }

    /**
     * Create an empty database on the server, failing if one of this name already exists.
     */
    public function create(string $name): void
    {
        $this->server()->getSchemaBuilder()->createDatabase($this->validName($name));
    }

    /**
     * Drop a database from the server if it exists, closing every connection this process holds to it first.
     */
    public function drop(string $name): void
    {
        $this->disconnect();

        $this->server()->getSchemaBuilder()->dropDatabaseIfExists($this->validName($name));
    }

    /**
     * Register the build connection, the template connection pointed at the given database, and return its name.
     */
    public function buildConnection(string $name): string
    {
        config(['database.connections.'.self::BUILD_CONNECTION => array_merge(
            $this->templateConfig(),
            ['database' => $this->validName($name)]
        )]);

        $this->db->purge(self::BUILD_CONNECTION);

        return self::BUILD_CONNECTION;
    }

    /**
     * Close the build connection and the tenant connection, so no session of this process stays on a tenant database.
     */
    public function disconnect(): void
    {
        $this->db->purge(self::BUILD_CONNECTION);

        $this->db->purge(config('tenancy.database.connection'));
    }

    /**
     * The server connection: the configured one, or a copy of the template on a session of its own, so creating a
     * database, which commits any open transaction on MySQL, never touches a transaction in progress.
     */
    protected function server(): Connection
    {
        $configured = config('tenancy.database.server_connection');

        if (filled($configured)) {
            return $this->db->connection($configured);
        }

        config(['database.connections.'.self::SERVER_CONNECTION => $this->templateConfig()]);

        return $this->db->connection(self::SERVER_CONNECTION);
    }

    /**
     * The configuration of the connection every tenant connection is built from.
     *
     * @return array<string, mixed>
     */
    protected function templateConfig(): array
    {
        $template = config('tenancy.database.template') ?: config('database.default');

        if (
            $template === config('tenancy.database.connection')
            || ! is_array($config = config("database.connections.{$template}"))
        ) {
            throw new TenancyMisconfiguredException("The tenant connection template [{$template}] must be a configured connection other than the tenant connection.");
        }

        return $config;
    }

    /**
     * The name given, refused unless it is made only of letters, numbers and underscores.
     */
    protected function validName(string $name): string
    {
        if (! preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException("[{$name}] is not a valid tenant database name.");
        }

        return $name;
    }
}
