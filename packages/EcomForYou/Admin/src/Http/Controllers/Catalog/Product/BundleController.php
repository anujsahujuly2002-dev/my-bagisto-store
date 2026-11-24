<?php

namespace EcomForYou\Admin\Http\Controllers\Catalog\Product;

use EcomForYou\Admin\Http\Controllers\Controller;
use EcomForYou\Product\Helpers\BundleOption;
use EcomForYou\Product\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class BundleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected BundleOption $bundleOptionHelper
    ) {}

    /**
     * Returns the compare items of the customer.
     */
    public function options(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $this->bundleOptionHelper->getBundleConfig($product),
        ]);
    }
}
