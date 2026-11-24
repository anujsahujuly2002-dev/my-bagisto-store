<?php

namespace EcomForYou\Theme\Jobs;

use EcomForYou\Theme\Exceptions\InvalidThemeArchive;
use EcomForYou\Theme\Upload\ThemeInstaller;
use EcomForYou\Theme\Upload\ThemeUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Throwable;

class InstallUploadedTheme implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted, once, since a failed install would only fail again.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $uploadId,
        public string $archivePath,
        public string $originalName,
        public ?int $adminId = null
    ) {}

    /**
     * Install the archive, recording how it went for the admin waiting on it.
     */
    public function handle(ThemeInstaller $themeInstaller, ThemeUploads $themeUploads): void
    {
        try {
            $theme = $themeInstaller->install($this->archivePath, $this->originalName);

            $themeUploads->installed($this->uploadId, $this->adminId, $theme->name);
        } catch (InvalidThemeArchive $exception) {
            $themeUploads->failed($this->uploadId, $this->adminId, $exception->reason, $exception->replace);
        } finally {
            File::delete($this->archivePath);
        }
    }

    /**
     * Record a failure the installer did not foresee, and remove the waiting archive.
     */
    public function failed(?Throwable $exception): void
    {
        app(ThemeUploads::class)->failed($this->uploadId, $this->adminId, 'failed');

        File::delete($this->archivePath);
    }
}
