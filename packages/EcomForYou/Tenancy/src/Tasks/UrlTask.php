<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\UrlGenerator;

class UrlTask implements SwitchingTask
{
    /**
     * The application url the single store used before any switch.
     */
    protected string $originalUrl;

    /**
     * Create the task, remembering the single store's application url.
     */
    public function __construct(protected Application $app)
    {
        $this->originalUrl = (string) config('app.url');
    }

    /**
     * Set the application url to the tenant's, and outside a request root every generated link there, on the
     * tenant's own scheme rather than the console's.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        config(['app.url' => $tenant->url()]);

        if ($this->app->runningInConsole()) {
            $this->urls()->forceRootUrl($tenant->url());

            $this->urls()->forceScheme(config('tenancy.scheme'));
        }
    }

    /**
     * Put the single store's application url back.
     */
    public function forgetCurrent(): void
    {
        config(['app.url' => $this->originalUrl]);

        if ($this->app->runningInConsole()) {
            $this->urls()->forceRootUrl(null);

            $this->urls()->forceScheme(null);
        }
    }

    /**
     * The url generator.
     */
    protected function urls(): UrlGenerator
    {
        return $this->app['url'];
    }
}
