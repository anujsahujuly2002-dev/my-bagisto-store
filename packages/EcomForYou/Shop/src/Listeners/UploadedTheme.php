<?php

namespace EcomForYou\Shop\Listeners;

use EcomForYou\Theme\Upload\UploadedPage;
use EcomForYou\Theme\ViewRenderEventManager;

class UploadedTheme
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected UploadedPage $uploadedPage) {}

    /**
     * Add the uploaded template's stylesheets to the layout's head.
     *
     * @return void
     */
    public function addStyles(ViewRenderEventManager $viewRenderEventManager)
    {
        if (! empty($this->uploadedPage->current()['styles'])) {
            $viewRenderEventManager->addTemplate('shop::uploaded-theme.styles');
        }
    }

    /**
     * Add the uploaded template's scripts after the storefront app mounts, so they find the markup Vue rendered.
     *
     * @return void
     */
    public function addScripts(ViewRenderEventManager $viewRenderEventManager)
    {
        if (! empty($this->uploadedPage->current()['scripts'])) {
            $viewRenderEventManager->addTemplate('shop::uploaded-theme.scripts');
        }
    }
}
