<?php

namespace EcomForYou\Tenancy\Tasks;

use Closure;
use EcomForYou\Core\Filesystem\StorageConfigurator;
use EcomForYou\Tenancy\Exceptions\NoTenantSelectedException;
use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Contracts\Foundation\Application;

class FilesystemTask implements SwitchingTask
{
    /**
     * The driver a store disk uses while no tenant is current, which refuses to be built.
     */
    public const UNAVAILABLE_DRIVER = 'tenancy-unavailable';

    /**
     * The container key `tenant_storage_path()` reads the current store's local root from.
     */
    public const LOCAL_ROOT = 'path.tenant_storage';

    /**
     * The configuration each tenant disk had before any switch.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $originals = [];

    /**
     * The default disk before any switch.
     */
    protected ?string $originalDefault;

    /**
     * Create the task, remembering the single store's default disk and its configuration of every tenant disk.
     */
    public function __construct(protected Application $app)
    {
        $this->originalDefault = config('filesystems.default');

        foreach (array_keys(config('tenancy.filesystem.disks', [])) as $disk) {
            if (is_array($original = config("filesystems.disks.{$disk}"))) {
                $this->originals[$disk] = $original;
            }
        }
    }

    /**
     * Root every tenant disk and the local root in the tenant's own folder, or under the tenant's own prefix on a
     * remote disk, give a public disk the tenant's url, then apply the disk and credentials the store itself chose.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->useLocalRoot(fn () => storage_path($this->forTenant(config('tenancy.filesystem.local_root'), $tenant)));

        foreach ($this->originals as $disk => $original) {
            $paths = config("tenancy.filesystem.disks.{$disk}");

            $config = array_merge($original, [
                'root' => $this->rootFor($original, $paths['root'], $tenant),
            ]);

            if (isset($paths['url'])) {
                $config['url'] = $tenant->url().$this->forTenant($paths['url'], $tenant);
            }

            config(["filesystems.disks.{$disk}" => $config]);
        }

        config(['filesystems.default' => $this->originalDefault]);

        $this->app->make(StorageConfigurator::class)->configure();

        $this->forgetDisks();
    }

    /**
     * Make every tenant disk and the local root refuse to be used while tenancy is on, or give the single store
     * its disks and root back.
     */
    public function forgetCurrent(): void
    {
        $this->useLocalRoot(config('tenancy.enabled')
            ? fn () => throw new NoTenantSelectedException('files')
            : null);

        foreach ($this->originals as $disk => $original) {
            config(["filesystems.disks.{$disk}" => config('tenancy.enabled')
                ? array_merge($original, ['driver' => self::UNAVAILABLE_DRIVER])
                : $original]);
        }

        config(['filesystems.default' => $this->originalDefault]);

        $this->forgetDisks();
    }

    /**
     * The tenant's own local folders, the local root and each local tenant disk's root, found without making it
     * current. A remote disk's prefix is not a folder and is left out.
     *
     * @return list<string>
     */
    public function directories(Tenant $tenant): array
    {
        $paths = [config('tenancy.filesystem.local_root')];

        foreach ($this->originals as $disk => $original) {
            if ($this->isLocal($original)) {
                $paths[] = config("tenancy.filesystem.disks.{$disk}.root");
            }
        }

        return array_map(fn (string $path) => storage_path($this->forTenant($path, $tenant)), $paths);
    }

    /**
     * Fill a configured path in for a tenant.
     */
    protected function forTenant(string $path, Tenant $tenant): string
    {
        return str_replace('{id}', (string) $tenant->id, $path);
    }

    /**
     * A disk's root for the tenant: a folder under `storage/` for a local disk, or the tenant's prefix appended to
     * the disk's own prefix for a remote one.
     *
     * @param  array<string, mixed>  $original
     */
    protected function rootFor(array $original, string $path, Tenant $tenant): string
    {
        $path = $this->forTenant($path, $tenant);

        if ($this->isLocal($original)) {
            return storage_path($path);
        }

        return ltrim(trim((string) ($original['root'] ?? ''), '/').'/'.trim($path, '/'), '/');
    }

    /**
     * Whether a disk keeps its files on this server.
     *
     * @param  array<string, mixed>  $original
     */
    protected function isLocal(array $original): bool
    {
        return ($original['driver'] ?? null) === 'local';
    }

    /**
     * Bind the local root to the given resolver, or unbind it so the single store's root applies again.
     */
    protected function useLocalRoot(?Closure $root): void
    {
        unset($this->app[self::LOCAL_ROOT]);

        if ($root) {
            $this->app->bind(self::LOCAL_ROOT, $root);
        }
    }

    /**
     * Drop every resolved tenant disk and the default one, so the next use builds each from its new configuration.
     */
    protected function forgetDisks(): void
    {
        $this->app['filesystem']->forgetDisk(array_values(array_unique(array_filter([
            ...array_keys($this->originals),
            config('filesystems.default'),
        ]))));
    }
}
