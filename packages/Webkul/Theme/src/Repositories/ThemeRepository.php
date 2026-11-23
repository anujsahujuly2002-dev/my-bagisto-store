<?php

namespace Webkul\Theme\Repositories;

use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Theme\Contracts\Theme;

class ThemeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Theme::class;
    }

    /**
     * Return the enabled themes of every panel, keyed by panel and then by theme code, as config entries.
     */
    public function enabledConfigs(): array
    {
        return $this->model
            ->newQuery()
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->groupBy('panel')
            ->map(fn (Collection $themes) => $themes->mapWithKeys(fn ($theme) => [$theme->code => $theme->toConfig()])->all())
            ->all();
    }
}
