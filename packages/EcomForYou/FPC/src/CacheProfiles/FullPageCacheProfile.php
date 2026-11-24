<?php

namespace EcomForYou\FPC\CacheProfiles;

use EcomForYou\Theme\ThemePreview;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Throwable;

class FullPageCacheProfile extends CacheAllSuccessfulGetRequests
{
    /**
     * Whether the page cache runs for this request, as Full Page Cache configuration decides, never for a signed-in
     * customer, whose pages carry their own account state, nor for an admin previewing a theme no one else sees.
     */
    public function enabled(Request $request): bool
    {
        if (
            ! config('responsecache.enabled')
            || auth()->guard('customer')->check()
            || app(ThemePreview::class)->isActive()
        ) {
            return false;
        }

        return (bool) $this->setting('enabled', true);
    }

    /**
     * How long a freshly rendered page stays in the cache.
     */
    public function cacheLifetimeInSeconds(Request $request): int
    {
        $minutes = (int) $this->setting('lifetime', 0);

        return $minutes > 0
            ? $minutes * 60
            : (int) config('responsecache.cache.lifetime_in_seconds');
    }

    /**
     * Read a Full Page Cache setting, falling back when the store is not yet readable,
     * as it is not during installation or on a console request.
     */
    protected function setting(string $field, mixed $default): mixed
    {
        try {
            $value = core()->getConfigData('cache_management.full_page_cache.settings.'.$field);
        } catch (Throwable) {
            return $default;
        }

        return is_null($value) || $value === '' ? $default : $value;
    }
}
