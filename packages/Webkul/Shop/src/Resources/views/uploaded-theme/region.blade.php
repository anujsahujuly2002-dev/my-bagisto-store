@if ($region !== 'main')
    {!! view_render_event('bagisto.shop.layout.'.$region.'.before') !!}
@endif

@foreach (app(\Webkul\Theme\Upload\UploadedPage::class)->current()['regions'][$region] ?? [] as $part)
    @if ($part['type'] === 'html')
        {!! $part['html'] !!}
    @elseif (in_array($part['slot'] ?? null, \Webkul\Theme\Upload\HtmlConverter::SLOTS, true))
        @include('shop::uploaded-theme.slots.'.$part['slot'], $part['params'] ?? [])
    @endif
@endforeach

@if ($region !== 'main')
    {!! view_render_event('bagisto.shop.layout.'.$region.'.after') !!}
@endif
