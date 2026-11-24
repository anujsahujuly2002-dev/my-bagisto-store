<?php

namespace EcomForYou\CatalogRule\Console\Commands;

use EcomForYou\CatalogRule\Helpers\CatalogRuleIndex;
use Illuminate\Console\Command;

class PriceRuleIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:price-rule:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically updates catalog rule price index information (eg. rule_price)';

    /**
     * Create a new command instance.
     *
     * @param  \EcomForYou\CatalogRuleProduct\Helpers\CatalogRuleIndex  $catalogRuleIndexHelper
     * @return void
     */
    public function __construct(protected CatalogRuleIndex $catalogRuleIndexHelper)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->catalogRuleIndexHelper->reIndexComplete();
    }
}
