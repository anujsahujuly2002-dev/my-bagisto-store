<?php

namespace EcomForYou\Theme\Upload;

use EcomForYou\Theme\Theme;
use Illuminate\Support\Facades\File;

class UploadedPage
{
    /**
     * The converted pages read in this request, keyed by the views path and image overrides they were read for.
     *
     * @var array
     */
    protected $pages = [];

    /**
     * Return the converted page of the theme the storefront renders with, or null when it was not uploaded.
     */
    public function current(): ?array
    {
        return $this->for(themes()->current());
    }

    /**
     * Return the converted page of a theme with its placeholders resolved, or null when it was not uploaded.
     */
    public function for(?Theme $theme): ?array
    {
        if (
            ! $theme
            || blank($theme->viewsPath)
        ) {
            return null;
        }

        $key = $theme->viewsPath.'|'.md5(json_encode(config('themes.shop.'.$theme->code.'.images', [])));

        return $this->pages[$key] ??= $this->read($theme);
    }

    /**
     * Read a release's page from beside its views, resolving its placeholders.
     */
    protected function read(Theme $theme): ?array
    {
        $file = dirname(base_path($theme->viewsPath)).'/'.ThemeInstaller::PAGE_FILE;

        if (! File::exists($file)) {
            return null;
        }

        $page = json_decode(File::get($file), true);

        if (! is_array($page)) {
            return null;
        }

        $images = $this->imageReplacements($theme, $page['images'] ?? []);

        $replacements = [
            HtmlConverter::ASSET_PLACEHOLDER => rtrim(bagisto_theme_storage()->url($theme->assetsPath), '/').'/',
            HtmlConverter::HOME_PLACEHOLDER => route('shop.home.index'),
        ];

        array_walk_recursive($page, function (&$value, $key) use ($images, $replacements) {
            if (
                is_string($value)
                && in_array($key, ['html', 'href', 'src', 'css'], true)
            ) {
                $value = strtr(strtr($value, $images), $replacements);
            }
        });

        return $page;
    }

    /**
     * Map each editable image's placeholder to the file an admin put in its place, or to its template url.
     */
    protected function imageReplacements(Theme $theme, array $images): array
    {
        $overrides = config('themes.shop.'.$theme->code.'.images', []);

        return collect($images)
            ->mapWithKeys(fn ($image) => [
                HtmlConverter::IMAGE_PLACEHOLDER.$image['id'].'__' => isset($overrides[$image['id']])
                    ? bagisto_theme_storage()->url($overrides[$image['id']])
                    : $image['src'],
            ])
            ->all();
    }
}
