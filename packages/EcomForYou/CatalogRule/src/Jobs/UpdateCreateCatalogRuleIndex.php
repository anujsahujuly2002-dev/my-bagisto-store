<?php

namespace EcomForYou\CatalogRule\Jobs;

use EcomForYou\CatalogRule\Contracts\CatalogRule;
use EcomForYou\CatalogRule\Helpers\CatalogRuleIndex;
use EcomForYou\DataTransfer\Jobs\Concerns\RetriesOnDeadlock;
use EcomForYou\Product\Helpers\Indexers\Price as PriceIndexer;
use EcomForYou\Product\Repositories\ProductRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class UpdateCreateCatalogRuleIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RetriesOnDeadlock, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected CatalogRule $catalogRule) {}

    /**
     * Reindex the rule and the prices of the products it applies to and of the composite products built from them;
     * for a disabled rule, rules an `end_other_rules` rule held back are not reapplied yet.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->catalogRule->status) {
            $this->retryOnDeadlock(fn () => app(CatalogRuleIndex::class)->reIndexRule($this->catalogRule));

            $this->catalogRule->unsetRelation('catalog_rule_products');

            $productIds = $this->catalogRule->catalog_rule_products->pluck('product_id')->unique();
        } else {
            $productIds = $this->catalogRule->catalog_rule_products->pluck('product_id')->unique();

            $this->retryOnDeadlock(fn () => app(CatalogRuleIndex::class)->cleanProductIndices($productIds));
        }

        $productIds = $productIds
            ->merge(app(ProductRepository::class)->getCompositeParentIds($productIds->all()))
            ->unique()
            ->values()
            ->all();

        Event::dispatch('promotions.catalog_rule.reindex.before', [$productIds]);

        $this->retryOnDeadlock(fn () => app(PriceIndexer::class)->reindexProducts($productIds));

        Event::dispatch('promotions.catalog_rule.reindex.after', [$productIds]);
    }
}
