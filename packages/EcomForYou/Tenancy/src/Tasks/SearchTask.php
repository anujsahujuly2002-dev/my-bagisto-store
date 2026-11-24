<?php

namespace EcomForYou\Tenancy\Tasks;

use EcomForYou\Tenancy\Models\Tenant;

class SearchTask implements SwitchingTask
{
    /**
     * The search index prefix the single store used before any switch.
     */
    protected string $originalPrefix;

    /**
     * Create the task, remembering the single store's search index prefix.
     */
    public function __construct()
    {
        $this->originalPrefix = (string) config('elasticsearch.index_prefix');
    }

    /**
     * Prefix the tenant's search indices with `t{id}_`, or the prefix its record keeps in `data.index_prefix`.
     */
    public function makeCurrent(Tenant $tenant): void
    {
        config(['elasticsearch.index_prefix' => $tenant->data['index_prefix'] ?? 't'.$tenant->id.'_']);
    }

    /**
     * Put the single store's search index prefix back.
     */
    public function forgetCurrent(): void
    {
        config(['elasticsearch.index_prefix' => $this->originalPrefix]);
    }
}
