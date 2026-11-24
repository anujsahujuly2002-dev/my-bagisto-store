<?php

namespace EcomForYou\Theme\Upload;

use EcomForYou\Theme\Exceptions\InvalidThemeArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeArchive
{
    /**
     * The most files an archive may hold.
     */
    public const MAX_FILES = 2000;

    /**
     * The most bytes an archive may unpack to.
     */
    public const MAX_UNPACKED_BYTES = 200 * 1024 * 1024;

    /**
     * The most bytes read from a single entry without unpacking the archive.
     */
    public const MAX_READ_BYTES = 64 * 1024;

    /**
     * The file extensions a theme may ship, every other file being refused.
     */
    public const ALLOWED_EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'json', 'map', 'txt',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'otf', 'eot',
        'mp4', 'webm',
    ];

    /**
     * Fragments no segment of a path may carry, since a web server can run a file named after them.
     */
    public const FORBIDDEN_FRAGMENTS = ['.php', '.phtml', '.phar', '.pht', '.shtml', '.cgi'];

    /**
     * Entries archivers add on their own, skipped rather than refused.
     */
    public const IGNORED_PREFIXES = ['__MACOSX/'];

    /**
     * Check every entry of the archive, then unpack it into the destination and return the paths it holds.
     *
     * @throws InvalidThemeArchive
     */
    public function extract(string $archivePath, string $destination): array
    {
        $zip = $this->open($archivePath);

        try {
            $entries = $this->inspect($zip);

            $prefix = $this->sharedRoot(array_keys($entries));

            File::ensureDirectoryExists($destination);

            $paths = [];

            foreach ($entries as $name => $index) {
                $path = Str::after($name, $prefix);

                $this->unpack($zip, $index, $destination.'/'.$path);

                $paths[] = $path;
            }

            return $paths;
        } finally {
            $zip->close();
        }
    }

    /**
     * Check every entry of the archive without unpacking it, returning the paths it holds and the contents of
     * the named file when it is among them.
     *
     * @return array{0: array, 1: string|null}
     *
     * @throws InvalidThemeArchive
     */
    public function scan(string $archivePath, string $read): array
    {
        $zip = $this->open($archivePath);

        try {
            $entries = $this->inspect($zip);

            $prefix = $this->sharedRoot(array_keys($entries));

            $paths = array_map(fn ($name) => Str::after($name, $prefix), array_keys($entries));

            $index = $entries[$prefix.$read] ?? null;

            $contents = is_null($index) ? null : $zip->getFromIndex($index, static::MAX_READ_BYTES);

            return [$paths, $contents === false ? null : $contents];
        } finally {
            $zip->close();
        }
    }

    /**
     * Open the archive for reading.
     *
     * @throws InvalidThemeArchive
     */
    protected function open(string $archivePath): ZipArchive
    {
        $zip = new ZipArchive;

        if ($zip->open($archivePath, ZipArchive::RDONLY) !== true) {
            throw new InvalidThemeArchive('invalid-archive');
        }

        return $zip;
    }

    /**
     * Refuse the archive over any entry that is unsafe or too large, returning the file entries by name.
     *
     * @throws InvalidThemeArchive
     */
    protected function inspect(ZipArchive $zip): array
    {
        if ($zip->numFiles > static::MAX_FILES) {
            throw new InvalidThemeArchive('too-many-files', ['count' => static::MAX_FILES]);
        }

        $entries = [];

        $bytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);

            $name = $stat['name'];

            if ($this->isIgnored($name)) {
                continue;
            }

            $this->assertSafePath($name);

            if ($this->isSymlink($zip, $index)) {
                throw new InvalidThemeArchive('unsafe-path', ['path' => $name]);
            }

            if (str_ends_with($name, '/')) {
                continue;
            }

            $this->assertAllowedFile($name);

            $bytes += $stat['size'];

            if ($bytes > static::MAX_UNPACKED_BYTES) {
                throw new InvalidThemeArchive('too-large', ['size' => static::MAX_UNPACKED_BYTES / 1024 / 1024]);
            }

            $entries[$name] = $index;
        }

        if (empty($entries)) {
            throw new InvalidThemeArchive('missing-index');
        }

        return $entries;
    }

    /**
     * Refuse a path that is absolute, climbs out of the archive or names a hidden or executable file.
     *
     * @throws InvalidThemeArchive
     */
    protected function assertSafePath(string $name): void
    {
        $segments = explode('/', rtrim($name, '/'));

        $unsafe = $name === ''
            || str_contains($name, '\\')
            || str_contains($name, "\0")
            || str_starts_with($name, '/')
            || preg_match('/^[A-Za-z]:/', $name)
            || collect($segments)->contains(fn ($segment) => in_array($segment, ['', '.', '..'], true)
                || str_starts_with($segment, '.')
                || Str::contains(Str::lower($segment), self::FORBIDDEN_FRAGMENTS));

        if ($unsafe) {
            throw new InvalidThemeArchive('unsafe-path', ['path' => $name]);
        }
    }

    /**
     * Refuse a file whose extension a theme has no use for.
     *
     * @throws InvalidThemeArchive
     */
    protected function assertAllowedFile(string $name): void
    {
        $extension = Str::lower(pathinfo($name, PATHINFO_EXTENSION));

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidThemeArchive('disallowed-file', ['path' => $name]);
        }
    }

    /**
     * Whether an entry is one archivers add on their own.
     */
    protected function isIgnored(string $name): bool
    {
        return Str::startsWith($name, self::IGNORED_PREFIXES)
            || basename($name) === '.DS_Store';
    }

    /**
     * Whether an entry was stored as a symbolic link.
     */
    protected function isSymlink(ZipArchive $zip, int $index): bool
    {
        $zip->getExternalAttributesIndex($index, $system, $attributes);

        return $system === ZipArchive::OPSYS_UNIX
            && (($attributes >> 16) & 0170000) === 0120000;
    }

    /**
     * The single folder every entry sits in when the archive wraps the theme in one, so it unpacks at the root.
     */
    protected function sharedRoot(array $names): string
    {
        if (in_array('index.html', $names, true)) {
            return '';
        }

        $roots = collect($names)->map(fn ($name) => Str::before($name, '/').'/')->unique();

        if (
            $roots->count() === 1
            && collect($names)->every(fn ($name) => str_contains($name, '/'))
        ) {
            return $roots->first();
        }

        return '';
    }

    /**
     * Stream one entry to its destination, stopping at the size the archive declared for it.
     *
     * @throws InvalidThemeArchive
     */
    protected function unpack(ZipArchive $zip, int $index, string $target): void
    {
        $declared = $zip->statIndex($index)['size'];

        $source = $zip->getStreamIndex($index);

        if ($source === false) {
            throw new InvalidThemeArchive('invalid-archive');
        }

        File::ensureDirectoryExists(dirname($target));

        $handle = fopen($target, 'wb');

        try {
            $written = stream_copy_to_stream($source, $handle, $declared + 1);
        } finally {
            fclose($handle);

            fclose($source);
        }

        if ($written === false || $written > $declared) {
            throw new InvalidThemeArchive('invalid-archive');
        }
    }
}
