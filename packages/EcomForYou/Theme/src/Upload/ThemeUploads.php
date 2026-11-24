<?php

namespace EcomForYou\Theme\Upload;

use Illuminate\Support\Facades\Cache;

class ThemeUploads
{
    /**
     * An upload waiting for the queue to install it.
     */
    public const STATUS_QUEUED = 'queued';

    /**
     * An upload installed as a theme.
     */
    public const STATUS_INSTALLED = 'installed';

    /**
     * An upload the installer refused or failed on.
     */
    public const STATUS_FAILED = 'failed';

    /**
     * How long an upload's status is kept, in seconds.
     */
    public const TTL = 86400;

    /**
     * The prefix of the cache key an upload's status is kept under.
     */
    public const CACHE_PREFIX = 'theme.upload.';

    /**
     * The directory archives wait in until the queue installs them, in the current store's own folder.
     */
    public function directory(): string
    {
        return tenant_storage_path('theme-uploads/pending');
    }

    /**
     * Record that an upload by the given admin is waiting to be installed under the code.
     */
    public function queue(string $id, string $code, ?int $adminId): void
    {
        $this->put($id, [
            'status' => self::STATUS_QUEUED,
            'code' => $code,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * Record that an upload by the given admin was installed as the named theme.
     */
    public function installed(string $id, ?int $adminId, string $name): void
    {
        $this->put($id, [
            'status' => self::STATUS_INSTALLED,
            'admin_id' => $adminId,
            'name' => $name,
        ]);
    }

    /**
     * Record that an upload by the given admin failed, with the translation key of the reason and the values it names.
     */
    public function failed(string $id, ?int $adminId, string $reason, array $replace = []): void
    {
        $this->put($id, [
            'status' => self::STATUS_FAILED,
            'admin_id' => $adminId,
            'reason' => $reason,
            'replace' => $replace,
        ]);
    }

    /**
     * Return an upload's status, or an empty array when it is unknown or has expired.
     */
    public function find(string $id): array
    {
        $status = Cache::get(self::CACHE_PREFIX.$id);

        return is_array($status) ? $status : [];
    }

    /**
     * Store an upload's status.
     */
    protected function put(string $id, array $status): void
    {
        Cache::put(self::CACHE_PREFIX.$id, $status, self::TTL);
    }
}
