<?php

namespace EcomForYou\CatalogRule\Jobs;

use EcomForYou\DataTransfer\Jobs\Concerns\RetriesOnDeadlock;
use EcomForYou\Product\Helpers\Indexers\Price as PriceIndexer;
use EcomForYou\Product\Repositories\ProductRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class DeleteCatalogRuleIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RetriesOnDeadlock, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  array  $productIds
     * @return void
     */
    public function __construct(protected $productIds) {}

    /**
     * Reindex the prices of the products a removed rule applied to and of the composite products built from them;
     * rules an `end_other_rules` rule held back are not reapplied yet.
     *
     * @return void
     */
    public function handle()
    {
        $productIds = array_values(array_unique([
            ...$this->productIds,
            ...app(ProductRepository::class)->getCompositeParentIds($this->productIds),
        ]));

        Event::dispatch('promotions.catalog_rule.reindex.before', [$productIds]);

        $this->retryOnDeadlock(fn () => app(PriceIndexer::class)->reindexProducts($productIds));

        Event::dispatch('promotions.catalog_rule.reindex.after', [$productIds]);
    }
}
