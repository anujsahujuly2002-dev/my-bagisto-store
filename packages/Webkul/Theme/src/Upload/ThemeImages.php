<?php

namespace Webkul\Theme\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Theme\Contracts\Theme;
use Webkul\Theme\Repositories\SectionRepository;
use Webkul\Theme\Repositories\ThemeRepository;
use Webkul\Theme\Theme as ThemeView;

class ThemeImages
{
    /**
     * The file types an image can be replaced with.
     */
    public const MIMES = ['jpeg', 'jpg', 'png', 'webp', 'gif', 'avif'];

    /**
     * The largest replacement image accepted, in kilobytes.
     */
    public const MAX_KILOBYTES = 5120;

    /**
     * Create a new theme images instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeRepository $themeRepository,
        protected UploadedPage $uploadedPage,
        protected SectionRepository $sectionRepository
    ) {}

    /**
     * Return the editable images of an uploaded theme's current release, each with its template url and the
     * url it shows with now, leaving out the home page's own once its blocks became sections edited there,
     * unless every image is asked for.
     *
     * @param  Theme  $theme
     */
    public function all($theme, bool $editableOnly = true): array
    {
        $page = $this->uploadedPage->for(new ThemeView(
            $theme->code,
            $theme->name,
            $theme->assets_path,
            $theme->views_path,
        ));

        $overrides = $theme->manifest['images'] ?? [];

        $hasSections = $editableOnly && $this->hasSections($theme->code);

        return collect($page['images'] ?? [])
            ->reject(fn ($image) => $hasSections && ($image['region'] ?? null) === 'main')
            ->map(fn ($image) => $image + [
                'url' => isset($overrides[$image['id']]) ? bagisto_theme_storage()->url($overrides[$image['id']]) : $image['src'],
                'is_replaced' => isset($overrides[$image['id']]),
            ])
            ->values()
            ->all();
    }

    /**
     * Whether the theme's current release has an editable image of this id.
     *
     * @param  Theme  $theme
     */
    public function exists($theme, string $id): bool
    {
        return collect($this->all($theme))->contains('id', $id);
    }

    /**
     * Put an uploaded file in place of one of the theme's images, removing the file it replaces.
     *
     * @param  Theme  $theme
     * @return Theme
     */
    public function replace($theme, string $id, UploadedFile $file)
    {
        $path = $file->storeAs(
            $this->directory($theme->code),
            $id.'-'.Str::lower(Str::random(8)).'.'.$file->extension()
        );

        return $this->store($theme, $id, $path);
    }

    /**
     * Return one of the theme's images to the file its template ships.
     *
     * @param  Theme  $theme
     * @return Theme
     */
    public function reset($theme, string $id)
    {
        return $this->store($theme, $id, null);
    }

    /**
     * The directory on the storage disk the replacement images of a theme are written to.
     */
    public function directory(string $code): string
    {
        return 'themes/'.$code.'/images';
    }

    /**
     * Whether any channel holds Appearance sections for the theme, which then draw its home page.
     */
    protected function hasSections(string $code): bool
    {
        return $this->sectionRepository->findWhere(['theme_code' => $code])->isNotEmpty();
    }

    /**
     * Record the file an image shows, or none to show the template's own, and remove the one it showed before.
     *
     * @param  Theme  $theme
     * @return Theme
     */
    protected function store($theme, string $id, ?string $path)
    {
        Event::dispatch('appearance.theme.image.update.before', $theme->id);

        $manifest = $theme->manifest ?? [];

        $images = $manifest['images'] ?? [];

        if (isset($images[$id])) {
            Storage::delete($images[$id]);
        }

        if (is_null($path)) {
            unset($images[$id]);
        } else {
            $images[$id] = $path;
        }

        $theme = $this->themeRepository->update(['manifest' => ['images' => $images] + $manifest], $theme->id);

        Event::dispatch('appearance.theme.image.update.after', $theme);

        return $theme;
    }
}
