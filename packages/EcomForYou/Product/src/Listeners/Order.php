<?php

namespace EcomForYou\Product\Listeners;

use EcomForYou\Product\Helpers\Indexers\Flat as FlatIndexer;
use EcomForYou\Product\Jobs\UpdateCreateInventoryIndex as UpdateCreateInventoryIndexJob;

class Order
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected FlatIndexer $flatIndexer) {}

    /**
     * After order is created.
     *
     * @param  \EcomForYou\Sale\Contracts\Order  $order
     * @return void
     */
    public function afterCancelOrCreate($order)
    {
        $productIds = $order->all_items
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->flatIndexer->refreshDerivedColumns($productIds);

        UpdateCreateInventoryIndexJob::dispatch($productIds);
    }
}
