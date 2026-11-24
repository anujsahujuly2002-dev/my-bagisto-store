<x-shop::products.carousel
    :title="$title"
    :src="route('shop.api.products.index', array_merge([$filter => 1, 'limit' => $limit], $catalogQuery ?? []))"
    :navigation-link="route('shop.search.index', [$filter => 1])"
    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
/>
