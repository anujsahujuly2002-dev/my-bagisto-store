<a
    class="bagisto-logo"
    href="{{ route('shop.home.index') }}"
    aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.bagisto')"
>
    <img
        src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
        alt="{{ core()->getCurrentChannel()->logo_alt ?: config('app.name') }}"
    >
</a>
