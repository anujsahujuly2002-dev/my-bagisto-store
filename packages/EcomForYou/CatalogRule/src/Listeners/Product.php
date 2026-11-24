<?php

namespace EcomForYou\CatalogRule\Listeners;

use EcomForYou\CatalogRule\Jobs\UpdateCreateProductIndex as UpdateCreateProductIndexJob;

class Product
{
    /**
     * @param  \EcomForYou\Product\Contracts\Product  $product
     * @return void
     */
    public function afterUpdate($product)
    {
        UpdateCreateProductIndexJob::dispatch($product);
    }
}
