<?php

namespace EcomForYou\Product\Jobs\Search;

use EcomForYou\Product\Services\Search\SearchEngineManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $productIds) {}

    /**
     * Execute the job.
     */
    public function handle(SearchEngineManager $manager): void
    {
        $manager->indexer()->deleteBatch($this->productIds);
    }
}
