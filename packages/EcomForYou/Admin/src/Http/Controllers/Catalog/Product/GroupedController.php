<?php

namespace EcomForYou\Admin\Http\Controllers\Catalog\Product;

use EcomForYou\Admin\Http\Controllers\Controller;
use EcomForYou\Product\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class GroupedController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Returns the compare items of the customer.
     */
    public function options(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        $options = $product->grouped_products()->orderBy('sort_order')->get();

        $products = [];

        foreach ($options as $option) {
            if (! $option->associated_product->getTypeInstance()->isSaleable()) {
                continue;
            }

            $products[] = [
                'id' => $option->associated_product->id,
                'name' => $option->associated_product->name,
                'qty' => $option->qty,
                'price' => $price = $option->associated_product->getTypeInstance()->getFinalPrice(),
                'formatted_price' => core()->formatBasePrice($price),
            ];
        }

        return new JsonResponse([
            'data' => $products,
        ]);
    }
}
