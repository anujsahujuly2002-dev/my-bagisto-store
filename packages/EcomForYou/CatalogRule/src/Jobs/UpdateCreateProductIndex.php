<?php

namespace EcomForYou\CatalogRule\Jobs;

use EcomForYou\CatalogRule\Helpers\CatalogRuleIndex;
use EcomForYou\DataTransfer\Jobs\Concerns\RetriesOnDeadlock;
use EcomForYou\Product\Contracts\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCreateProductIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RetriesOnDeadlock, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  Product  $product
     * @return void
     */
    public function __construct(protected $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->retryOnDeadlock(fn () => app(CatalogRuleIndex::class)->reIndexProduct($this->product));
    }
}
