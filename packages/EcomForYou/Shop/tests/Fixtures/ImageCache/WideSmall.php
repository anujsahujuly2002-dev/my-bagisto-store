<?php

namespace EcomForYou\Shop\Tests\Fixtures\ImageCache;

use EcomForYou\ImageCache\Templates\Small;

class WideSmall extends Small
{
    /**
     * The width for small images.
     */
    protected int $width = 400;

    /**
     * The height for small images.
     */
    protected int $height = 100;
}
