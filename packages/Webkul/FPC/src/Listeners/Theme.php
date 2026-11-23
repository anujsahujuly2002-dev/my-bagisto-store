<?php

namespace Webkul\FPC\Listeners;

use Spatie\ResponseCache\Facades\ResponseCache;

class Theme
{
    /**
     * Drop every cached page after an uploaded theme changes, since its header and footer are on all of them.
     *
     * @return void
     */
    public function afterUpdate()
    {
        ResponseCache::clear();
    }
}
