<?php

namespace EcomForYou\Product\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Product\Models\Product;
use EcomForYou\Product\Models\ProductAttributeValue;
use EcomForYou\Product\Models\ProductBundleOption;
use EcomForYou\Product\Models\ProductBundleOptionProduct;
use EcomForYou\Product\Models\ProductBundleOptionTranslation;
use EcomForYou\Product\Models\ProductCustomerGroupPrice;
use EcomForYou\Product\Models\ProductCustomizableOption;
use EcomForYou\Product\Models\ProductCustomizableOptionPrice;
use EcomForYou\Product\Models\ProductCustomizableOptionTranslation;
use EcomForYou\Product\Models\ProductDownloadableLink;
use EcomForYou\Product\Models\ProductDownloadableSample;
use EcomForYou\Product\Models\ProductFlat;
use EcomForYou\Product\Models\ProductGroupedProduct;
use EcomForYou\Product\Models\ProductImage;
use EcomForYou\Product\Models\ProductImageTranslation;
use EcomForYou\Product\Models\ProductInventory;
use EcomForYou\Product\Models\ProductInventoryIndex;
use EcomForYou\Product\Models\ProductOrderedInventory;
use EcomForYou\Product\Models\ProductPriceIndex;
use EcomForYou\Product\Models\ProductReview;
use EcomForYou\Product\Models\ProductReviewAttachment;
use EcomForYou\Product\Models\ProductSalableInventory;
use EcomForYou\Product\Models\ProductVideo;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Product::class,
        ProductAttributeValue::class,
        ProductBundleOption::class,
        ProductBundleOptionProduct::class,
        ProductBundleOptionTranslation::class,
        ProductCustomerGroupPrice::class,
        ProductCustomizableOption::class,
        ProductCustomizableOptionPrice::class,
        ProductCustomizableOptionTranslation::class,
        ProductDownloadableLink::class,
        ProductDownloadableSample::class,
        ProductFlat::class,
        ProductGroupedProduct::class,
        ProductImage::class,
        ProductImageTranslation::class,
        ProductInventory::class,
        ProductInventoryIndex::class,
        ProductOrderedInventory::class,
        ProductPriceIndex::class,
        ProductReview::class,
        ProductReviewAttachment::class,
        ProductSalableInventory::class,
        ProductVideo::class,
    ];
}
