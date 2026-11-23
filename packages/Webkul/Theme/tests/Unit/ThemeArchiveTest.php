<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Webkul\Theme\Exceptions\InvalidThemeArchive;
use Webkul\Theme\Upload\ThemeArchive;

/**
 * Build a zip of the given entries in a temporary file, storing the names in `$symlinks` as symbolic links.
 */
function archiveFixture(array $entries, array $symlinks = []): string
{
    $path = sys_get_temp_dir().'/theme-archive-'.Str::random(12).'.zip';

    $zip = new ZipArchive;

    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);
    }

    foreach ($symlinks as $name => $target) {
        $zip->addFromString($name, $target);

        $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0120777 << 16);
    }

    $zip->close();

    return $path;
}

/**
 * Extract an archive into a fresh directory, returning the reason it was refused, or the paths it held.
 */
function extractFixture(string $archive, ?ThemeArchive $themeArchive = null): array|string
{
    $destination = sys_get_temp_dir().'/theme-extract-'.Str::random(12);

    try {
        return ($themeArchive ?? new ThemeArchive)->extract($archive, $destination);
    } catch (InvalidThemeArchive $exception) {
        return $exception->reason;
    } finally {
        File::deleteDirectory($destination);

        File::delete($archive);
    }
}

// ============================================================================
// Extracting
// ============================================================================

it('should unpack every file of a valid archive', function () {
    $paths = extractFixture(archiveFixture([
        'index.html' => '<h1>Home</h1>',
        'css/style.css' => 'body {}',
        'images/logo.png' => 'png',
    ]));

    expect($paths)->toEqualCanonicalizing(['index.html', 'css/style.css', 'images/logo.png']);
});

it('should unpack an archive whose files sit in a single folder at the root', function () {
    $paths = extractFixture(archiveFixture([
        'my-template/index.html' => '<h1>Home</h1>',
        'my-template/css/style.css' => 'body {}',
    ]));

    expect($paths)->toEqualCanonicalizing(['index.html', 'css/style.css']);
});

it('should skip the entries archivers add on their own', function () {
    $paths = extractFixture(archiveFixture([
        'index.html' => '<h1>Home</h1>',
        '__MACOSX/._index.html' => 'junk',
        'css/.DS_Store' => 'junk',
    ]));

    expect($paths)->toBe(['index.html']);
});

it('should write the files it unpacks to the destination', function () {
    $archive = archiveFixture(['index.html' => '<h1>Home</h1>', 'css/style.css' => 'body {}']);

    $destination = sys_get_temp_dir().'/theme-extract-'.Str::random(12);

    (new ThemeArchive)->extract($archive, $destination);

    expect(File::get($destination.'/css/style.css'))->toBe('body {}');

    File::deleteDirectory($destination);

    File::delete($archive);
});

// ============================================================================
// Refusing
// ============================================================================

it('should refuse an entry that is unsafe', function (string $name, string $reason) {
    expect(extractFixture(archiveFixture(['index.html' => 'ok', $name => 'x'])))->toBe($reason);
})->with([
    'parent directory' => ['../escape.html', 'unsafe-path'],
    'nested parent directory' => ['css/../../escape.css', 'unsafe-path'],
    'absolute path' => ['/etc/escape.css', 'unsafe-path'],
    'drive letter' => ['C:/escape.css', 'unsafe-path'],
    'backslash' => ['css\\escape.css', 'unsafe-path'],
    'php script' => ['shell.php', 'unsafe-path'],
    'php under a second extension' => ['shell.php.css', 'unsafe-path'],
    'phar' => ['lib/tool.phar', 'unsafe-path'],
    'htaccess' => ['.htaccess', 'unsafe-path'],
    'hidden file' => ['css/.hidden.css', 'unsafe-path'],
    'executable' => ['tools/setup.exe', 'disallowed-file'],
    'blade view' => ['views/home.blade', 'disallowed-file'],
    'no extension' => ['README', 'disallowed-file'],
]);

it('should refuse an entry stored as a symbolic link', function () {
    expect(extractFixture(archiveFixture(['index.html' => 'ok'], ['css/link.css' => '/etc/passwd'])))->toBe('unsafe-path');
});

it('should refuse a file that is not a zip archive', function () {
    $path = sys_get_temp_dir().'/theme-archive-'.Str::random(12).'.zip';

    File::put($path, 'not a zip');

    expect(extractFixture($path))->toBe('invalid-archive');
});

it('should refuse an archive holding too many files', function () {
    $themeArchive = new class extends ThemeArchive
    {
        /**
         * A limit low enough to reach in a test.
         */
        public const MAX_FILES = 2;
    };

    expect(extractFixture(archiveFixture(['index.html' => 'a', 'a.css' => 'b', 'b.css' => 'c']), $themeArchive))->toBe('too-many-files');
});

it('should refuse an archive that unpacks to more than its limit', function () {
    $themeArchive = new class extends ThemeArchive
    {
        /**
         * A limit low enough to reach in a test.
         */
        public const MAX_UNPACKED_BYTES = 10;
    };

    expect(extractFixture(archiveFixture(['index.html' => str_repeat('a', 11)]), $themeArchive))->toBe('too-large');
});
