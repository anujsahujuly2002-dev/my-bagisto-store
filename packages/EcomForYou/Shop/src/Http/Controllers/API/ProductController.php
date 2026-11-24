<?php

namespace EcomForYou\Shop\Http\Controllers\API;

use EcomForYou\Category\Repositories\CategoryRepository;
use EcomForYou\Marketing\Jobs\UpdateCreateSearchTerm as UpdateCreateSearchTermJob;
use EcomForYou\Product\Enums\SearchContextEnum;
use EcomForYou\Product\Repositories\ProductRepository;
use EcomForYou\Shop\Helpers\CatalogApiCache;
use EcomForYou\Shop\Http\Resources\ProductCardResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends APIController
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected CatalogApiCache $catalogApiCache
    ) {}

    /**
     * Product listings.
     */
    public function index(): JsonResource|JsonResponse
    {
        $searchData = $this->resolveSearchQueryData();

        $query = $searchData['effective_query'] ?? $searchData['original_query'];

        /**
         * Search results are never cached: the search-term space is unbounded
         * and the search-term job must run on every request.
         */
        if (! empty($query)) {
            $products = $this->getProducts($query);

            /**
             * Update or create search term only if
             * there is only one filter that is query param
             */
            if (count(request()->except(['mode', 'sort', 'limit'])) == 1) {
                UpdateCreateSearchTermJob::dispatch([
                    'term' => $query,
                    'results' => $products->total(),
                    'channel_id' => core()->getCurrentChannel()->id,
                    'locale' => app()->getLocale(),
                ]);
            }

            return ProductCardResource::collection($products);
        }

        /**
         * Listing/carousel responses (no search term) are cached per catalog
         * version, so repeated homepage visits skip the database entirely.
         */
        $data = $this->catalogApiCache->remember('products', request()->query(), function () {
            return ProductCardResource::collection($this->getProducts(''))
                ->response()
                ->getData(true);
        });

        return response()->json($data)->withHeaders($this->catalogCacheHeaders());
    }

    /**
     * Fetch the storefront product listing for the given query.
     */
    protected function getProducts(string $query)
    {
        return $this->productRepository
            ->setSearchContext(SearchContextEnum::STOREFRONT)
            ->getAll(array_merge(request()->query(), [
                'query' => $query,
                'channel_id' => core()->getCurrentChannel()->id,
                'status' => 1,
                'visible_individually' => 1,
            ]));
    }

    /**
     * Resolve search query data.
     */
    protected function resolveSearchQueryData(): array
    {
        if (request()->query('suggest', '') === '0') {
            return [
                'original_query' => request()->query('query', ''),
                'effective_query' => null,
            ];
        }

        $originalQuery = request()->query('query', '');

        return [
            'original_query' => $originalQuery,
            'effective_query' => $this->getEffectiveQuery($originalQuery),
        ];
    }

    /**
     * It will return the effective query based on the search engine.
     */
    protected function getEffectiveQuery(string $originalQuery): ?string
    {
        return $this->productRepository->setSearchContext(SearchContextEnum::STOREFRONT)->getSuggestions($originalQuery);
    }

    /**
     * Related product listings.
     *
     * @param  int  $id
     */
    public function relatedProducts($id): JsonResource
    {
        $product = $this->productRepository->findOrFail($id);

        $relatedProducts = $product->related_products()
            ->take(core()->getConfigData('catalog.products.product_view_page.no_of_related_products'))
            ->get();

        return ProductCardResource::collection($relatedProducts);
    }

    /**
     * Up-sell product listings.
     *
     * @param  int  $id
     */
    public function upSellProducts($id): JsonResource
    {
        $product = $this->productRepository->findOrFail($id);

        $upSellProducts = $product->up_sells()
            ->take(core()->getConfigData('catalog.products.product_view_page.no_of_up_sells_products'))
            ->get();

        return ProductCardResource::collection($upSellProducts);
    }
}
