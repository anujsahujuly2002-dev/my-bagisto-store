<?php

namespace EcomForYou\Theme;

use EcomForYou\Core\Contracts\Channel;
use EcomForYou\Core\Repositories\ChannelRepository;
use Illuminate\Support\Facades\Event;
use Prettus\Repository\Events\RepositoryEntityUpdated;

class ThemeActivator
{
    /**
     * Create a new activator instance.
     *
     * @return void
     */
    public function __construct(protected ChannelRepository $channelRepository) {}

    /**
     * Point a single channel at the theme, firing the events a channel update fires.
     *
     * @param  Channel  $channel
     */
    public function activate($channel, string $code): void
    {
        Event::dispatch('appearance.theme.activate.before', $channel->id);

        Event::dispatch('core.channel.update.before', $channel->id);

        $channel->theme = $code;

        $channel->save();

        Event::dispatch(new RepositoryEntityUpdated($this->channelRepository, $channel));

        Event::dispatch('core.channel.update.after', $channel);

        Event::dispatch('appearance.theme.activate.after', $channel);
    }
}
