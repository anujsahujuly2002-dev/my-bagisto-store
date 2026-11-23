<form
    class="bagisto-search"
    action="{{ route('shop.search.index') }}"
    role="search"
>
    <input
        type="search"
        name="query"
        value="{{ request('query') }}"
        placeholder="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
        aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.search-text')"
        required
    >

    <button type="submit">
        @lang('shop::app.components.layouts.header.desktop.bottom.search')
    </button>
</form>
