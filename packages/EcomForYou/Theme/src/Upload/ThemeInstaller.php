<?php

namespace EcomForYou\Theme\Upload;

use EcomForYou\Core\Repositories\ChannelRepository;
use EcomForYou\Theme\Contracts\Theme;
use EcomForYou\Theme\Enums\SectionTypeEnum;
use EcomForYou\Theme\Exceptions\InvalidThemeArchive;
use EcomForYou\Theme\Models\Theme as ThemeModel;
use EcomForYou\Theme\Repositories\SectionRepository;
use EcomForYou\Theme\Repositories\ThemeRepository;
use EcomForYou\Theme\SectionSchema;
use EcomForYou\Theme\ThemeRegistry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class ThemeInstaller
{
    /**
     * The page of the template that becomes the storefront's home page.
     */
    public const INDEX = 'index.html';

    /**
     * The optional file at the root of the archive that names the theme.
     */
    public const MANIFEST = 'theme.json';

    /**
     * The file of a release that holds its converted page.
     */
    public const PAGE_FILE = 'page.json';

    /**
     * The file of a release that records the name and version it was uploaded with.
     */
    public const RELEASE_FILE = 'release.json';

    /**
     * The files at the root of an archive taken as its screenshot when the manifest names none.
     */
    public const SCREENSHOTS = ['screenshot.png', 'screenshot.jpg', 'screenshot.jpeg', 'screenshot.webp', 'screenshot.svg'];

    /**
     * The file extensions a screenshot may have.
     */
    public const SCREENSHOT_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'avif'];

    /**
     * The pattern a theme code must match.
     */
    public const CODE_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * The longest theme code accepted.
     */
    public const CODE_MAX_LENGTH = 64;

    /**
     * The view each converted region overrides in the default theme, with the stub written there.
     */
    public const STUBS = [
        'header' => ['components/layouts/header/index.blade.php', "@include('shop::uploaded-theme.region', ['region' => 'header'])\n"],
        'footer' => ['components/layouts/footer/index.blade.php', "@include('shop::uploaded-theme.region', ['region' => 'footer'])\n"],
        'main' => ['home/index.blade.php', "@include('shop::uploaded-theme.home')\n"],
    ];

    /**
     * Create a new installer instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeArchive $themeArchive,
        protected HtmlConverter $htmlConverter,
        protected ThemeRepository $themeRepository,
        protected ThemeRegistry $themeRegistry,
        protected ChannelRepository $channelRepository,
        protected SectionRepository $sectionRepository,
        protected SectionSchema $sectionSchema
    ) {}

    /**
     * Check an uploaded archive without unpacking it and return the code it would install under, so a bad
     * archive is refused before it is queued.
     *
     * @throws InvalidThemeArchive
     */
    public function prepare(string $archivePath, string $originalName): string
    {
        [$files, $manifest] = $this->themeArchive->scan($archivePath, self::MANIFEST);

        if (! in_array(self::INDEX, $files, true)) {
            throw new InvalidThemeArchive('missing-index');
        }

        $manifest = in_array(self::MANIFEST, $files, true) ? $this->parseManifest((string) $manifest) : [];

        return $this->resolveCode($manifest['code'] ?? null, $originalName);
    }

    /**
     * Install an uploaded template archive as a storefront theme, replacing an earlier upload of the same code.
     *
     * @return Theme
     *
     * @throws InvalidThemeArchive
     */
    public function install(string $archivePath, string $originalName)
    {
        $staging = storage_path('app/theme-uploads/'.Str::uuid());

        try {
            $files = $this->themeArchive->extract($archivePath, $staging);

            if (! in_array(self::INDEX, $files, true)) {
                throw new InvalidThemeArchive('missing-index');
            }

            $manifest = in_array(self::MANIFEST, $files, true)
                ? $this->parseManifest(File::get($staging.'/'.self::MANIFEST))
                : [];

            $code = $this->resolveCode($manifest['code'] ?? null, $originalName);

            $manifest['screenshot'] = $this->resolveScreenshot($manifest['screenshot'] ?? null, $files);

            Event::dispatch('appearance.theme.upload.before', $code);

            $page = $this->htmlConverter->convert(File::get($staging.'/'.self::INDEX), $files);

            $existing = $this->themeRepository->findOneWhere(['code' => $code, 'panel' => ThemeModel::PANEL_SHOP]);

            $release = now()->format('YmdHis').Str::lower(Str::random(6));

            $theme = $this->publish($code, $release, $staging, $files, $page, $manifest, $existing);

            $this->importSections($theme, $page);

            Event::dispatch('appearance.theme.upload.after', $theme);

            return $theme;
        } finally {
            File::deleteDirectory($staging);
        }
    }

    /**
     * The local directory an uploaded theme's releases are written under.
     */
    public function directory(string $code): string
    {
        return storage_path('app/themes/'.$code);
    }

    /**
     * The path on the storage disk an uploaded theme's assets are written under.
     */
    public function assetDirectory(string $code): string
    {
        return 'themes/'.$code.'/assets';
    }

    /**
     * Whether the uploaded theme still holds the files of the release before its current one.
     *
     * @param  Theme  $theme
     */
    public function canRollback($theme): bool
    {
        $previous = $theme->manifest['previous_release'] ?? null;

        return $theme->source === ThemeModel::SOURCE_UPLOAD
            && is_string($previous)
            && File::isDirectory($this->releaseDirectory($theme->code, $previous).'/views');
    }

    /**
     * Point an uploaded theme back at its previous release, which then becomes the one to roll forward to.
     *
     * @param  Theme  $theme
     * @return Theme
     */
    public function rollback($theme)
    {
        Event::dispatch('appearance.theme.rollback.before', $theme->id);

        $current = $theme->manifest['release'];

        $previous = $theme->manifest['previous_release'];

        $directory = $this->releaseDirectory($theme->code, $previous);

        $page = $this->readJson($directory.'/'.self::PAGE_FILE);

        $details = $this->readJson($directory.'/'.self::RELEASE_FILE);

        $theme = $this->themeRepository->update([
            'name' => $details['name'] ?? $theme->name,
            'version' => array_key_exists('version', $details) ? $details['version'] : $theme->version,
            'assets_path' => $this->assetDirectory($theme->code).'/'.$previous,
            'views_path' => $this->relativeViewsPath($directory),
            'manifest' => [
                'release' => $previous,
                'previous_release' => $current,
                'title' => $page['title'] ?? null,
                'screenshot' => $details['screenshot'] ?? null,
                'images' => $theme->manifest['images'] ?? [],
            ],
        ], $theme->id);

        Event::dispatch('appearance.theme.rollback.after', $theme);

        return $theme;
    }

    /**
     * Remove an uploaded theme with every release of its views and assets.
     *
     * @param  Theme  $theme
     */
    public function uninstall($theme): void
    {
        Event::dispatch('appearance.theme.delete.before', $theme->id);

        $this->themeRepository->delete($theme->id);

        File::deleteDirectory($this->directory($theme->code));

        Storage::deleteDirectory('themes/'.$theme->code);

        Event::dispatch('appearance.theme.delete.after', $theme->id);
    }

    /**
     * Write the release's views, page and assets, then point the theme at it and prune older releases.
     *
     * @param  Theme|null  $existing
     * @return Theme
     */
    protected function publish(string $code, string $release, string $staging, array $files, array $page, array $manifest, $existing)
    {
        $releaseDirectory = $this->releaseDirectory($code, $release);

        $assetDirectory = $this->assetDirectory($code).'/'.$release;

        try {
            $this->writeViews($releaseDirectory, $page);

            File::put($releaseDirectory.'/'.self::PAGE_FILE, json_encode($page, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $name = $manifest['name'] ?? Str::headline($code);

            $version = $manifest['version'] ?? null;

            $screenshot = $manifest['screenshot'] ?? null;

            File::put($releaseDirectory.'/'.self::RELEASE_FILE, json_encode(compact('name', 'version', 'screenshot'), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->copyAssets($staging, $files, $assetDirectory);

            $attributes = [
                'name' => $name,
                'version' => $version,
                'parent' => 'default',
                'assets_path' => $assetDirectory,
                'views_path' => $this->relativeViewsPath($releaseDirectory),
                'vite' => null,
                'manifest' => [
                    'release' => $release,
                    'previous_release' => $existing?->manifest['release'] ?? null,
                    'title' => $page['title'],
                    'screenshot' => $screenshot,
                    'images' => $existing?->manifest['images'] ?? [],
                ],
                'source' => ThemeModel::SOURCE_UPLOAD,
                'status' => true,
            ];

            $theme = $existing
                ? $this->themeRepository->update($attributes, $existing->id)
                : $this->themeRepository->create($attributes + ['code' => $code, 'panel' => ThemeModel::PANEL_SHOP]);
        } catch (Throwable $exception) {
            File::deleteDirectory($releaseDirectory);

            Storage::deleteDirectory($assetDirectory);

            throw $exception;
        }

        $this->prune($code, array_filter([$release, $theme->manifest['previous_release'] ?? null]));

        return $theme;
    }

    /**
     * The local directory one release of an uploaded theme is written to.
     */
    protected function releaseDirectory(string $code, string $release): string
    {
        return $this->directory($code).'/releases/'.$release;
    }

    /**
     * The views path of a release as a theme records it, relative to the application root.
     */
    protected function relativeViewsPath(string $releaseDirectory): string
    {
        return ltrim(Str::after($releaseDirectory.'/views', base_path()), '/');
    }

    /**
     * Read a JSON file a release holds, or nothing when it is missing or unreadable.
     */
    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Parse the manifest an archive ships, keeping only the values it may set.
     *
     * @throws InvalidThemeArchive
     */
    protected function parseManifest(string $contents): array
    {
        try {
            $manifest = json_decode($contents, true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidThemeArchive('invalid-manifest');
        }

        if (! is_array($manifest)) {
            throw new InvalidThemeArchive('invalid-manifest');
        }

        return collect(['name' => 100, 'code' => self::CODE_MAX_LENGTH, 'version' => 20, 'screenshot' => 255])
            ->map(fn ($length, $key) => is_scalar($manifest[$key] ?? null) ? Str::limit(trim((string) $manifest[$key]), $length, '') : null)
            ->filter(fn ($value) => filled($value))
            ->all();
    }

    /**
     * Settle the theme's code from the manifest or the archive's file name, refusing one a built-in theme holds.
     *
     * @throws InvalidThemeArchive
     */
    protected function resolveCode(?string $code, string $originalName): string
    {
        $code ??= Str::slug(pathinfo($originalName, PATHINFO_FILENAME));

        if (
            strlen($code) > self::CODE_MAX_LENGTH
            || ! preg_match(self::CODE_PATTERN, $code)
        ) {
            throw new InvalidThemeArchive('invalid-code', ['code' => $code]);
        }

        if ($this->themeRegistry->isConfigured($code)) {
            throw new InvalidThemeArchive('code-taken', ['code' => $code]);
        }

        return $code;
    }

    /**
     * Create the Appearance sections the home page's blocks describe on every channel that holds none for the
     * theme yet, so an admin can edit, reorder and hide them, and a later upload leaves their edits alone.
     *
     * @param  Theme  $theme
     */
    protected function importSections($theme, array $page): void
    {
        $blocks = $page['blocks'] ?? [];

        if (empty($blocks)) {
            return;
        }

        $locales = core()->getAllLocales()->pluck('code')->all();

        $replacements = $this->sectionReplacements($theme, $page);

        foreach ($this->channelRepository->all() as $channel) {
            $exists = $this->sectionRepository->findWhere([
                'channel_id' => $channel->id,
                'theme_code' => $theme->code,
            ])->isNotEmpty();

            if ($exists) {
                continue;
            }

            foreach (array_values($blocks) as $position => $block) {
                $type = $block['type'] === 'product_carousel'
                    ? SectionTypeEnum::PRODUCT_CAROUSEL->value
                    : SectionTypeEnum::STATIC_CONTENT->value;

                $options = $this->sectionSchema->type($theme->code, $type)?->sanitize($this->sectionOptions($block, $replacements)) ?? [];

                Event::dispatch('section.create.before');

                $section = $this->sectionRepository->create([
                    'type' => $type,
                    'name' => $block['name'] ?: trans('admin::app.appearance.themes.upload.section-name', ['number' => $position + 1]),
                    'sort_order' => $position + 1,
                    'status' => 1,
                    'channel_id' => $channel->id,
                    'theme_code' => $theme->code,
                ] + collect($locales)->mapWithKeys(fn ($locale) => [$locale => ['options' => $options]])->all());

                Event::dispatch('section.create.after', $section);
            }
        }
    }

    /**
     * The options a section is created with for a block of the home page.
     */
    protected function sectionOptions(array $block, array $replacements): array
    {
        if ($block['type'] === 'product_carousel') {
            return [
                'title' => $block['title'],
                'filters' => [
                    $block['filter'] => 1,
                    'limit' => $block['limit'],
                ],
            ];
        }

        return [
            'html' => str_replace('{{', "{\u{200B}{", strtr(strtr($block['html'], $replacements[0]), $replacements[1])),
            'css' => '',
        ];
    }

    /**
     * The placeholders a block's markup carries, mapped first to each image's template url and then to the
     * references and links stored markup keeps.
     *
     * @param  Theme  $theme
     */
    protected function sectionReplacements($theme, array $page): array
    {
        $images = collect($page['images'] ?? [])
            ->mapWithKeys(fn ($image) => [HtmlConverter::IMAGE_PLACEHOLDER.$image['id'].'__' => $image['src']])
            ->all();

        return [$images, [
            HtmlConverter::ASSET_PLACEHOLDER => bagisto_theme_storage()->mediaReference($theme->assets_path).'/',
            HtmlConverter::HOME_PLACEHOLDER => '/',
        ]];
    }

    /**
     * Settle the screenshot of an archive, the image its manifest names or a screenshot file at its root, or none.
     */
    protected function resolveScreenshot(?string $path, array $files): ?string
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), './');

        if (
            in_array($path, $files, true)
            && in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), self::SCREENSHOT_EXTENSIONS, true)
        ) {
            return $path;
        }

        return collect(self::SCREENSHOTS)->first(fn ($candidate) => in_array($candidate, $files, true));
    }

    /**
     * Write the stub views that hand each converted region of the release to the storefront's renderer.
     */
    protected function writeViews(string $releaseDirectory, array $page): void
    {
        foreach (self::STUBS as $region => [$path, $stub]) {
            if (is_null($page['regions'][$region])) {
                continue;
            }

            File::ensureDirectoryExists(dirname($releaseDirectory.'/views/'.$path));

            File::put($releaseDirectory.'/views/'.$path, $stub);
        }
    }

    /**
     * Copy every file of the archive but its pages and manifest to the storage disk.
     */
    protected function copyAssets(string $staging, array $files, string $assetDirectory): void
    {
        foreach ($files as $path) {
            if (
                $path === self::MANIFEST
                || in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['html', 'htm'], true)
            ) {
                continue;
            }

            $stream = fopen($staging.'/'.$path, 'rb');

            try {
                Storage::put($assetDirectory.'/'.$path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
    }

    /**
     * Remove every release of the theme but the ones named, locally and on the storage disk.
     */
    protected function prune(string $code, array $keep): void
    {
        foreach (File::directories($this->directory($code).'/releases') as $directory) {
            if (! in_array(basename($directory), $keep, true)) {
                File::deleteDirectory($directory);
            }
        }

        foreach (Storage::directories($this->assetDirectory($code)) as $directory) {
            if (! in_array(basename($directory), $keep, true)) {
                Storage::deleteDirectory($directory);
            }
        }
    }
}
