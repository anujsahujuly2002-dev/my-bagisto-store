<?php

namespace EcomForYou\Theme;

use EcomForYou\Theme\Models\Theme;
use EcomForYou\Theme\Repositories\ThemeRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class ThemeRegistry
{
    /**
     * The cache key the stored themes are kept under.
     */
    public const CACHE_KEY = 'theme.registry';

    /**
     * The panels a stored theme can be registered for.
     */
    public const PANELS = [Theme::PANEL_SHOP, Theme::PANEL_ADMIN];

    /**
     * Each panel's themes as `config/themes.php` registers them, captured before the first merge.
     *
     * @var array|null
     */
    protected $configured = null;

    /**
     * Create a new registry instance.
     *
     * @return void
     */
    public function __construct(protected ThemeRepository $themeRepository) {}

    /**
     * Merge the stored themes into each panel's config, keeping a configured theme over a stored one of the same code.
     */
    public function merge(): void
    {
        $this->configured ??= collect(self::PANELS)
            ->mapWithKeys(fn ($panel) => [$panel => Config::get('themes.'.$panel, [])])
            ->all();

        $stored = $this->stored();

        foreach (self::PANELS as $panel) {
            Config::set('themes.'.$panel, array_merge($stored[$panel] ?? [], $this->configured[$panel]));
        }
    }

    /**
     * Put back each panel's themes as `config/themes.php` registers them, dropping every stored one.
     */
    public function restore(): void
    {
        if (is_null($this->configured)) {
            return;
        }

        foreach (self::PANELS as $panel) {
            Config::set('themes.'.$panel, $this->configured[$panel]);
        }
    }

    /**
     * Whether `config/themes.php` registers a theme of this code for the panel, whatever the database holds.
     */
    public function isConfigured(string $code, string $panel = Theme::PANEL_SHOP): bool
    {
        $configured = $this->configured[$panel] ?? Config::get('themes.'.$panel, []);

        return array_key_exists($code, $configured);
    }

    /**
     * Forget the cached themes, so the next request reads them from the database again.
     */
    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Return the stored themes keyed by panel, from the cache when it holds them.
     */
    protected function stored(): array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        if (! Schema::hasTable('themes')) {
            return [];
        }

        $stored = $this->themeRepository->enabledConfigs();

        Cache::forever(self::CACHE_KEY, $stored);

        return $stored;
    }
}
