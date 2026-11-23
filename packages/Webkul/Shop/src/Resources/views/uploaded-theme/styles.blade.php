<style>
    :where(.bagisto-categories) { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 1.25rem; margin: 0; padding: 0; list-style: none; }
    :where(.bagisto-categories a) { color: inherit; text-decoration: none; white-space: nowrap; }
    :where(.bagisto-search) { display: flex; align-items: center; gap: 0.5rem; }
    :where(.bagisto-search input) { min-width: 0; }
    :where(.bagisto-logo) { display: inline-flex; align-items: center; }
    :where(.bagisto-logo img) { display: block; width: auto; max-height: 40px; }
    :where(.bagisto-account) { color: inherit; text-decoration: none; }
    :where(.bagisto-cart) { display: inline-flex; align-items: center; }
</style>

@foreach (app(\Webkul\Theme\Upload\UploadedPage::class)->current()['styles'] ?? [] as $style)
    @if (isset($style['href']))
        <link
            rel="stylesheet"
            href="{{ $style['href'] }}"
        />
    @elseif (isset($style['css']))
        <style>
            {!! $style['css'] !!}
        </style>
    @endif
@endforeach
