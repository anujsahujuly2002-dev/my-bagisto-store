<?php

namespace EcomForYou\Theme\Listeners;

use EcomForYou\Theme\ThemeRegistry;
use Illuminate\Contracts\Foundation\Application;

class Tenant
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected Application $app,
        protected ThemeRegistry $themeRegistry
    ) {}

    /**
     * Register the store's own themes once its tenant is current, and drop the view paths found for any other.
     */
    public function madeCurrent(): void
    {
        $this->flushViewFinder();

        $this->themeRegistry->merge();
    }

    /**
     * Put back the configured themes alone once no tenant is current, so no store's themes outlive it.
     */
    public function forgotten(): void
    {
        $this->themeRegistry->restore();

        $this->flushViewFinder();
    }

    /**
     * Forget the views the finder has located, which may lie in another store's uploaded theme.
     */
    protected function flushViewFinder(): void
    {
        $this->app['view.finder']->flush();
    }
}
