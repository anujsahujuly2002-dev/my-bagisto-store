<?php

namespace EcomForYou\Admin\Tests\Fixtures\Sections;

use EcomForYou\Theme\Sections\FooterLinks;

class NarrowFooterLinks extends FooterLinks
{
    /**
     * Most columns the theme's footer lays out.
     */
    protected ?int $maxColumns = 3;
}
