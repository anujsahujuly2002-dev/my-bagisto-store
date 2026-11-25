<?php

namespace EcomForYou\Sitemap\Helpers;

use Illuminate\Support\Facades\Storage;

class SitemapFileUrl
{
    /**
     * The url a sitemap file on the public disk is served from on a channel: the channel's base url and the
     * path the disk publishes the file under, which is the current store's own folder under tenancy.
     */
    public function forChannel(string $baseUrl, string $file): string
    {
        return rtrim($baseUrl, '/').'/'.$this->pathOnSite($file);
    }

    /**
     * The path the public disk publishes a file under, relative to the directory the application is served
     * from, which the channel's base url already carries.
     */
    protected function pathOnSite(string $file): string
    {
        $file = ltrim($file, '/');

        $published = parse_url((string) Storage::disk('public')->url($file), PHP_URL_PATH);

        if (
            ! is_string($published)
            || $published === ''
        ) {
            return 'storage/'.$file;
        }

        $base = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if (
            $base !== ''
            && str_starts_with($published, $base.'/')
        ) {
            $published = substr($published, strlen($base));
        }

        return ltrim($published, '/');
    }
}
