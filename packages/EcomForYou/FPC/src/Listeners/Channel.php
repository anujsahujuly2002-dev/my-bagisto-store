<?php

namespace EcomForYou\FPC\Listeners;

use Spatie\ResponseCache\Facades\ResponseCache;

class Channel
{
    /**
     * After channel update.
     *
     * @param  \EcomForYou\Core\Contracts\Channel  $channel
     * @return void
     */
    public function afterUpdate($channel)
    {
        ResponseCache::clear();
    }
}
