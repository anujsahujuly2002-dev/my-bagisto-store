<?php

namespace EcomForYou\Product\Observers;

use EcomForYou\Product\Contracts\Product;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    /**
     * Handle the Product "deleted" event.
     *
     * @param  Product  $product
     * @return void
     */
    public function deleted($product)
    {
        Storage::deleteDirectory('products/'.$product->id);
    }
}
