<?php

namespace EcomForYou\Shop\Http\Middleware;

use Closure;
use EcomForYou\Theme\ThemePreview;
use Illuminate\Http\Request;

class Theme
{
    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct(protected ThemePreview $themePreview) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->applyPreview($request);

        $themes = themes();

        $channel = core()->getCurrentChannel();

        if (
            $channel
            && $channelThemeCode = $channel->theme
        ) {
            $themes->exists($channelThemeCode)
                ? $themes->set($channelThemeCode)
                : $themes->set(config('themes.shop-default'));
        } else {
            $themes->set(config('themes.shop-default'));
        }

        return $next($request);
    }

    /**
     * Render the request with the theme a signed in admin is previewing, through a copy of the channel so
     * everything that reads its theme follows, and keep the page out of the response cache.
     *
     * @param  Request  $request
     */
    protected function applyPreview($request): void
    {
        $code = $this->themePreview->code();

        $channel = core()->getCurrentChannel();

        if (
            is_null($code)
            || ! $channel
        ) {
            return;
        }

        $request->attributes->set('responsecache.doNotCache', true);

        if ($channel->theme === $code) {
            return;
        }

        $previewed = clone $channel;

        $previewed->theme = $code;

        core()->setCurrentChannel($previewed);
    }
}
