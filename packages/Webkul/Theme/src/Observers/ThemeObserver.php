<?php

namespace Webkul\Theme\Observers;

use Webkul\Theme\ThemeRegistry;

class ThemeObserver
{
    /**
     * Create a new observer instance.
     *
     * @return void
     */
    public function __construct(protected ThemeRegistry $themeRegistry) {}

    /**
     * Handle the Theme "saved" event, so a change reaches the next request's config.
     *
     * @return void
     */
    public function saved()
    {
        $this->themeRegistry->forget();
    }

    /**
     * Handle the Theme "deleted" event, so a removed theme leaves the next request's config.
     *
     * @return void
     */
    public function deleted()
    {
        $this->themeRegistry->forget();
    }
}
