<?php

namespace EcomForYou\FPC\Tests\Fixtures\Sections;

use EcomForYou\Theme\Sections\SectionType;

class PromoBarSection extends SectionType
{
    /**
     * Code the section is stored under.
     */
    protected string $code = 'promo_bar';

    /**
     * Whether the layout draws the section on every page.
     */
    protected bool $layout = true;
}
