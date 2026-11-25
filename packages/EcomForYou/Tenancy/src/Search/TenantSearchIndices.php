<?php

namespace EcomForYou\Tenancy\Search;

use EcomForYou\Core\Facades\ElasticSearch;
use EcomForYou\Product\Services\Search\SearchEngineManager;
use EcomForYou\Tenancy\Models\Tenant;
use EcomForYou\Tenancy\Tasks\SearchTask;

class TenantSearchIndices
{
    /**
     * Delete every search index of the current store, by name, on the cluster its own settings point at. A store
     * that never turned an external engine on has none, and its cluster is left untouched, as is every index when the
     * store has no prefix of its own, which would match every other store's.
     *
     * @return list<string> The indices deleted.
     */
    public function delete(Tenant $tenant): array
    {
        $prefix = SearchTask::basePrefix($tenant);

        if (
            $prefix === ''
            || ! core()->getConfigData(SearchEngineManager::ENABLED_KEY)
        ) {
            return [];
        }

        $indices = array_keys(ElasticSearch::indices()->get([
            'index' => $prefix.'*',
            'ignore_unavailable' => true,
            'allow_no_indices' => true,
        ])->asArray());

        $indices = array_values(array_filter($indices, fn (string $index) => str_starts_with($index, $prefix)));

        if ($indices) {
            ElasticSearch::indices()->delete(['index' => implode(',', $indices)]);
        }

        return $indices;
    }
}
