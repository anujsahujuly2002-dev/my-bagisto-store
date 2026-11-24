<?php

namespace EcomForYou\Shop\Listeners;

use EcomForYou\Theme\ThemePreview;
use EcomForYou\Theme\ViewRenderEventManager;

class ThemePreviewBar
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected ThemePreview $themePreview) {}

    /**
     * Add the bar naming the previewed theme, with a way out, to the top of every storefront page.
     *
     * @return void
     */
    public function add(ViewRenderEventManager $viewRenderEventManager)
    {
        if ($this->themePreview->isActive()) {
            $viewRenderEventManager->addTemplate('shop::components.layouts.theme-preview');
        }
    }
}
