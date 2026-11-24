<?php

namespace EcomForYou\Tenancy\Tasks;

use Closure;
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
     * Create the task, remembering the single store's configuration of every tenant disk.
     */
    public function __construct(protected Application $app)
    {
        foreach (array_keys(config('tenancy.filesystem.disks', [])) as $disk) {
            if (is_array($original = config("filesystems.disks.{$disk}"))) {
                $this->originals[$disk] = $original;
            }
        }
    }

    /**
     * Root every tenant disk and the local root in the tenant's own folder, and give a public disk the tenant's url.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->useLocalRoot(fn () => storage_path($this->forTenant(config('tenancy.filesystem.local_root'), $tenant)));

        foreach ($this->originals as $disk => $original) {
            $paths = config("tenancy.filesystem.disks.{$disk}");

            $config = array_merge($original, [
                'root' => storage_path($this->forTenant($paths['root'], $tenant)),
            ]);

            if (isset($paths['url'])) {
                $config['url'] = $tenant->url().$this->forTenant($paths['url'], $tenant);
            }

            $this->useDisk($disk, $config);
        }
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
            $this->useDisk($disk, config('tenancy.enabled')
                ? array_merge($original, ['driver' => self::UNAVAILABLE_DRIVER])
                : $original);
        }
    }

    /**
     * Fill a configured path in for a tenant.
     */
    protected function forTenant(string $path, Tenant $tenant): string
    {
        return str_replace('{id}', (string) $tenant->id, $path);
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
     * Set a disk's configuration and drop the resolved disk, so the next use builds it afresh.
     *
     * @param  array<string, mixed>  $config
     */
    protected function useDisk(string $disk, array $config): void
    {
        config(["filesystems.disks.{$disk}" => $config]);

        $this->app['filesystem']->forgetDisk($disk);
    }
}
