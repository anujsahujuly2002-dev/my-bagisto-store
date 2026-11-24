@foreach ($sections as $section)
    @php
        $data = $section->options;

        $marks = ($preview ?? false)
            && ! $section->getTypeInstance()?->rendersInLayout();
    @endphp

    @if ($marks)
        <div
            data-section-id="{{ $section->id }}"
            data-section-name="{{ $section->name }}"
        >
    @endif

    <!-- Static Content -->
    @switch ($section->type)
        @case (\EcomForYou\Theme\Enums\SectionTypeEnum::IMAGE_CAROUSEL->value)
            <!-- Image Carousel -->
            <x-shop::carousel
                :options="$section->getTypeInstance()?->sanitize((array) $data) ?? $data"
                aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
            />

            @break
        @case (\EcomForYou\Theme\Enums\SectionTypeEnum::STATIC_CONTENT->value)
            <!-- Push Style -->
            @if (! empty($data['css']))
                @push ('styles')
                    <style>
                        {!! bagisto_theme_storage()->resolveMarkup($data['css']) !!}
                    </style>
                @endpush
            @endif

            <!-- Render HTML -->
            @if (! empty($data['html']))
                {!! bagisto_theme_storage()->resolveMarkup($data['html']) !!}
            @endif

            @break
        @case (\EcomForYou\Theme\Enums\SectionTypeEnum::CATEGORY_CAROUSEL->value)
            <!-- Categories Carousel -->
            <x-shop::categories.carousel
                :title="$data['title'] ?? ''"
                :src="route('shop.api.categories.index', array_merge((array) ($data['filters'] ?? []), $catalogQuery ?? []))"
                :navigation-link="route('shop.home.index')"
                aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
            />

            @break
        @case (\EcomForYou\Theme\Enums\SectionTypeEnum::PRODUCT_CAROUSEL->value)
            <!-- Product Carousel -->
            <x-shop::products.carousel
                :title="$data['title'] ?? ''"
                :src="route('shop.api.products.index', array_merge((array) ($data['filters'] ?? []), $catalogQuery ?? []))"
                :navigation-link="route('shop.search.index', $data['filters'] ?? [])"
                aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
            />

            @break
    @endswitch

    @if ($marks)
        </div>
    @endif
@endforeach
