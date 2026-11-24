<?php

namespace EcomForYou\Product\Listeners;

use EcomForYou\Product\Helpers\Indexers\Flat as FlatIndexer;
use EcomForYou\Product\Jobs\UpdateCreateInventoryIndex as UpdateCreateInventoryIndexJob;

class Refund
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected FlatIndexer $flatIndexer) {}

    /**
     * After refund is created.
     *
     * @param  \EcomForYou\Sale\Contracts\Refund  $refund
     * @return void
     */
    public function afterCreate($refund)
    {
        $productIds = $refund->items
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->flatIndexer->refreshDerivedColumns($productIds);

        UpdateCreateInventoryIndexJob::dispatch($productIds);
    }
}
