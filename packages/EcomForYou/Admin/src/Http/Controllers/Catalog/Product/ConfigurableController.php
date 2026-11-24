<?php

namespace EcomForYou\Admin\Http\Controllers\Catalog\Product;

use EcomForYou\Admin\Http\Controllers\Controller;
use EcomForYou\Product\Helpers\ConfigurableOption;
use EcomForYou\Product\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class ConfigurableController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ConfigurableOption $configurableOptionHelper
    ) {}

    /**
     * Get the attributes and variant index of a configurable product for the admin panel, without the
     * storefront's sized image urls, so an image swatch is its stored file.
     */
    public function options(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        $options = $this->configurableOptionHelper->getOptions(
            $product,
            $this->configurableOptionHelper->getAllowedVariants($product)
        );

        return new JsonResponse([
            'data' => [
                'attributes' => $this->configurableOptionHelper->getAttributesData($product, $options, false),
                'index' => $options['index'] ?? [],
            ],
        ]);
    }
}
