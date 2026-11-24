<?php

final class VendorRename
{
    /**
     * The vendor being replaced, written as code spells it.
     */
    public const FROM = 'Web'.'kul';

    /**
     * The vendor taking its place.
     */
    public const TO = 'EcomForYou';

    /**
     * Paths never rewritten: third-party code, lock files, build output, runtime files and the license, whose
     * copyright notice the MIT license requires to stay.
     */
    public const SKIP = [
        '#^vendor/#',
        '#(^|/)node_modules/#',
        '#^storage/#',
        '#^docs/#',
        '#^public/themes/[^/]+/[^/]+/build/#',
        '#^public/vendor/#',
        '#(^|/)composer\.lock$#',
        '#(^|/)package-lock\.json$#',
        '#^LICENSE$#',
        '#^scripts/rebrand/#',
        '#/Support/legacy-namespace\.php$#',
        '#_rename_vendor_in_stored_class_names\.php$#',
    ];

    /**
     * Brand text people see, keyed by the paths it is rewritten in: translation values, the page generator meta,
     * and the default application name. Commands, helpers, mailers and links keep the Bagisto name.
     */
    public const BRAND = [
        '#/Resources/lang/[^/]+/[^/]+\.php$#' => ['Bagisto' => self::TO],
        '#\.blade\.php$#' => [
            'content="Bagisto"' => 'content="'.self::TO.'"',
            "app_name ?? 'Bagisto'" => "app_name ?? '".self::TO."'",
        ],
        '#^config/app\.php$#' => ["env('APP_NAME', 'Bagisto')" => "env('APP_NAME', '".self::TO."')"],
        '#^\.env\.example$#' => ['APP_NAME=Bagisto' => 'APP_NAME='.self::TO],
    ];

    /**
     * File extensions treated as text and rewritten.
     */
    public const TEXT = ['php', 'json', 'js', 'ts', 'vue', 'md', 'xml', 'yml', 'yaml', 'neon', 'css', 'html', 'txt', 'sh', 'conf', 'ini', 'env', 'example', 'dist', 'stub', 'dockerfile'];

    /**
     * Create a new rename run over the repository at the given root.
     */
    public function __construct(private readonly string $root) {}

    /**
     * Rename the vendor and its brand text across the repository, safe to run again on each upstream release before
     * merging it: run `php scripts/rebrand/rename-vendor.php` from the repository root.
     */
    public function run(): void
    {
        $this->moveFolder();

        $changed = 0;

        foreach ($this->files() as $path) {
            $changed += $this->rewrite($path) ? 1 : 0;
        }

        fwrite(STDOUT, "Rewrote {$changed} files.".PHP_EOL);
    }

    /**
     * Rename `packages/<From>` to `packages/<To>`, keeping every file in it, tracked or not.
     */
    private function moveFolder(): void
    {
        $from = $this->root.'/packages/'.self::FROM;

        $to = $this->root.'/packages/'.self::TO;

        if (! is_dir($from)) {
            return;
        }

        if (is_dir($to)) {
            throw new RuntimeException('Both packages/'.self::FROM.' and packages/'.self::TO.' exist; merge them by hand first.');
        }

        if (! rename($from, $to)) {
            throw new RuntimeException('Could not move the package folder.');
        }

        fwrite(STDOUT, 'Moved packages/'.self::FROM.' to packages/'.self::TO.'.'.PHP_EOL);
    }

    /**
     * Every tracked or untracked, not ignored, text file of the repository that may be rewritten.
     *
     * @return list<string>
     */
    private function files(): array
    {
        exec('git -C '.escapeshellarg($this->root).' ls-files -co --exclude-standard -z', $output, $status);

        if ($status !== 0) {
            throw new RuntimeException('git ls-files failed.');
        }

        $paths = array_filter(explode("\0", implode("\n", $output)));

        return array_values(array_filter($paths, fn (string $path) => $this->isRewritable($path)));
    }

    /**
     * Whether a path is a text file outside every skipped location.
     */
    private function isRewritable(string $path): bool
    {
        foreach (self::SKIP as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        $name = strtolower(basename($path));

        $extension = str_contains($name, '.') ? substr($name, strrpos($name, '.') + 1) : $name;

        return is_file($this->root.'/'.$path)
            && (
                in_array($extension, self::TEXT, true)
                || str_starts_with($name, 'dockerfile')
                || str_ends_with($name, '.blade.php')
            );
    }

    /**
     * Apply every rename rule to one file, writing it back only when it changed.
     */
    private function rewrite(string $path): bool
    {
        $file = $this->root.'/'.$path;

        $original = file_get_contents($file);

        if ($original === false) {
            return false;
        }

        $updated = $original;

        if (stripos($updated, self::FROM) !== false) {
            $updated = strtr($updated, $this->literalRules());

            $updated = preg_replace_callback_array($this->patternRules(), $updated);
        }

        $updated = strtr($updated, $this->brandRules($path));

        if ($updated === $original) {
            return false;
        }

        file_put_contents($file, $updated);

        return true;
    }

    /**
     * Exact replacements, applied longest first: the namespace, package paths and package and image names.
     *
     * @return array<string, string>
     */
    private function literalRules(): array
    {
        $from = self::FROM;

        $to = self::TO;

        $lower = strtolower($from);

        $toLower = strtolower($to);

        return [
            $from.'\\' => $to.'\\',
            'packages/'.$from => 'packages/'.$to,
            $lower.'/bagisto' => $toLower.'/'.$toLower,
            '"'.$lower.'/' => '"'.$toLower.'/',
        ];
    }

    /**
     * The brand text replacements that apply to one path, merged from every matching entry of the brand map.
     *
     * @return array<string, string>
     */
    private function brandRules(string $path): array
    {
        $rules = [];

        foreach (self::BRAND as $pattern => $replacements) {
            if (preg_match($pattern, $path)) {
                $rules += $replacements;
            }
        }

        return $rules;
    }

    /**
     * Pattern replacements for what is left: maintainer emails at the old vendor's domain are dropped from
     * composer author entries and moved to example.com elsewhere, personal handles are dropped, links to the old
     * vendor's site go to a placeholder, and any other mention of the vendor's name becomes the new one.
     *
     * @return array<string, callable>
     */
    private function patternRules(): array
    {
        $from = preg_quote(self::FROM, '/');

        $lower = preg_quote(strtolower(self::FROM), '/');

        return [
            '/,\s*"email":\s*"[^"]*@'.$lower.'\.com"/i' => fn () => '',
            '/([A-Za-z0-9._%+-]+)@'.$lower.'\.com/i' => fn (array $match) => $match[1].'@example.com',
            '/\s*\(@[A-Za-z0-9-]+-'.$lower.'\)/i' => fn () => '',
            '/@[A-Za-z0-9-]+-'.$lower.'\b/i' => fn () => 'the maintainers',
            '/https?:\/\/(?:[a-z]+\.)?'.$lower.'\.com\/?[^\s\'")<]*/i' => fn () => 'https://example.com/',
            '/\b'.$from.'\b/' => fn () => self::TO,
            '/\b'.$lower.'\b/' => fn () => strtolower(self::TO),
            '/\b'.strtoupper(self::FROM).'\b/' => fn () => strtoupper(self::TO),
        ];
    }
}

(new VendorRename(dirname(__DIR__, 2)))->run();
