<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Product\Services\Search\SearchEngineConfigurator;
use EcomForYou\Tenancy\Models\Tenant;
use Illuminate\Contracts\Container\Container;

class SearchTask implements SwitchingTask
{
    /**
     * The search configuration the single store used before any switch.
     *
     * @var array<string, mixed>
     */
    protected array $original;

    /**
     * Create the task, remembering the single store's search configuration.
     */
    public function __construct(protected Container $app)
    {
        $this->original = (array) config('elasticsearch');
    }

    /**
     * Apply the search settings the store recorded, then confine its indices to its own prefix: `t{id}_` followed
     * by any prefix the store chose, so no store can name its way into another's indices on a shared cluster. A
     * prefix kept in the tenant's `data.index_prefix` is set by the platform and used as it is.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        config(['elasticsearch' => $this->original]);

        $this->app->make(SearchEngineConfigurator::class)->configure();

        $chosen = (string) config('elasticsearch.index_prefix');

        $chosen = $chosen === (string) ($this->original['index_prefix'] ?? '') ? '' : $chosen;

        config(['elasticsearch.index_prefix' => $tenant->data['index_prefix'] ?? 't'.$tenant->getKey().'_'.$chosen]);
    }

    /**
     * Put the single store's search configuration back.
     */
    public function forgetCurrent(): void
    {
        config(['elasticsearch' => $this->original]);
    }

    /**
     * The prefix every search index of the tenant starts with, whatever the store chose after it.
     */
    public static function basePrefix(Tenant $tenant): string
    {
        return $tenant->data['index_prefix'] ?? 't'.$tenant->getKey().'_';
    }
}
