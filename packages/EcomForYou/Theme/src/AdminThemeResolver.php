<?php

namespace EcomForYou\Theme;

use Illuminate\Support\Facades\File;

class AdminThemeResolver
{
    /**
     * The Configuration key of the store-wide admin theme.
     */
    public const CONFIG_KEY = 'general.design.admin_theme.code';

    /**
     * The resolved theme code, keyed by the signed in admin's id, so a request resolves once.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * The code of the admin theme to render with: the signed in admin's own choice, then the store setting,
     * then the configured default, skipping any whose views or build are missing.
     */
    public function resolve(): string
    {
        $user = auth()->guard('admin')->user();

        $key = $user?->id ?? 0;

        return $this->resolved[$key] ??= collect([
            $user?->theme,
            core()->getConfigData(self::CONFIG_KEY),
            $this->defaultCode(),
        ])->first(fn ($code) => $this->isUsable($code)) ?? $this->defaultCode();
    }

    /**
     * Whether a code names a configured admin theme whose views and build are on disk.
     */
    public function isUsable(mixed $code): bool
    {
        if (
            ! is_string($code)
            || blank($code)
        ) {
            return false;
        }

        $theme = config('themes.admin.'.$code);

        if (! is_array($theme)) {
            return false;
        }

        if ($code === $this->defaultCode()) {
            return true;
        }

        return $this->hasViews($theme)
            && $this->hasBuild($theme);
    }

    /**
     * The admin themes an admin can choose from, as select options.
     */
    public function getConfigOptions(): array
    {
        return collect(config('themes.admin', []))
            ->map(fn ($theme, $code) => [
                'title' => $theme['name'] ?? $code,
                'value' => $code,
            ])
            ->values()
            ->all();
    }

    /**
     * Forget what this request resolved, so a changed choice applies at once.
     */
    public function forget(): void
    {
        $this->resolved = [];
    }

    /**
     * The admin theme `config/themes.php` names as the default.
     */
    protected function defaultCode(): string
    {
        return config('themes.admin-default', 'default');
    }

    /**
     * Whether the theme's views directory exists, when it names one.
     */
    protected function hasViews(array $theme): bool
    {
        return blank($theme['views_path'] ?? null)
            || File::isDirectory(base_path($theme['views_path']));
    }

    /**
     * Whether the theme's Vite build or dev server is there, when it has a build of its own rather than its
     * parent's.
     */
    protected function hasBuild(array $theme): bool
    {
        $vite = $theme['vite'] ?? null;

        if (empty($vite)) {
            return filled($theme['parent'] ?? null);
        }

        return File::exists(public_path(trim($vite['build_directory'] ?? '', '/').'/manifest.json'))
            || (
                filled($vite['hot_file'] ?? null)
                && File::exists(public_path($vite['hot_file']))
            );
    }
}
