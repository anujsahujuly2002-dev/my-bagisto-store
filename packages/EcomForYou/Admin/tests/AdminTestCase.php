<?php

namespace EcomForYou\Admin\Tests;

use EcomForYou\Admin\Tests\Concerns\AdminTestBench;
use EcomForYou\Core\Tests\Concerns\CoreAssertions;
use EcomForYou\Product\Tests\Concerns\ProductTestBench;
use EcomForYou\Sales\Tests\Concerns\OrderTestBench;
use EcomForYou\Theme\Facades\Themes;
use EcomForYou\Theme\Themes as ThemeRegistry;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminTestCase extends TestCase
{
    use AdminTestBench, CoreAssertions, OrderTestBench, ProductTestBench;

    /**
     * Send a request with a theme registry resolved for that request alone, as it is in production.
     *
     * The registry decides once, when it is first resolved, whether the request is an admin one and only
     * registers that side's themes. A fixture that renders a storefront mail before the first admin
     * request, or an admin test that then renders the storefront preview, would otherwise carry the
     * themes of the wrong side into the request under test.
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        Themes::clearResolvedInstance(ThemeRegistry::class);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }
}
