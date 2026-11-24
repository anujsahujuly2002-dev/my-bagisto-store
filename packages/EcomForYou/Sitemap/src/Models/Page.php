<?php

namespace EcomForYou\Sitemap\Models;

use EcomForYou\CMS\Models\Page as BasePage;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Page extends BasePage implements Sitemapable
{
    /**
     * To get the sitemap tag for the CMS page.
     */
    public function toSitemapTag(): Url|string|array
    {
        if (! $this->url_key) {
            return [];
        }

        return Url::create(route('shop.cms.page', $this->url_key))
            ->setLastModificationDate(Carbon::create($this->updated_at));
    }
}
