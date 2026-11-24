<?php

namespace EcomForYou\Theme;

class ThemePreview
{
    /**
     * The session key the previewed theme code is kept under.
     */
    public const SESSION_KEY = 'appearance.theme_preview';

    /**
     * The permission an admin needs to preview a theme.
     */
    public const PERMISSION = 'appearance.themes';

    /**
     * Start previewing a theme on the storefront for the signed in admin's session.
     */
    public function start(string $code): void
    {
        session()->put(self::SESSION_KEY, $code);
    }

    /**
     * Stop previewing, so the session sees the theme its channel runs again.
     */
    public function stop(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * The code of the theme being previewed, or null unless a signed in admin who may preview asked for an
     * installed theme.
     */
    public function code(): ?string
    {
        if (! app()->bound('session')) {
            return null;
        }

        $code = session()->get(self::SESSION_KEY);

        if (
            ! is_string($code)
            || ! is_array(config('themes.shop.'.$code))
            || ! auth()->guard('admin')->check()
            || ! bouncer()->hasPermission(self::PERMISSION)
        ) {
            return null;
        }

        return $code;
    }

    /**
     * Whether this request renders a theme preview.
     */
    public function isActive(): bool
    {
        return ! is_null($this->code());
    }
}
