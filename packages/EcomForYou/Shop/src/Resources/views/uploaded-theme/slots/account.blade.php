@auth('customer')
    <a class="bagisto-account" href="{{ route('shop.customers.account.profile.index') }}">
        @lang('shop::app.components.layouts.header.desktop.bottom.profile')
    </a>
@else
    <a class="bagisto-account" href="{{ route('shop.customer.session.index') }}">
        @lang('shop::app.components.layouts.header.desktop.bottom.sign-in')
    </a>
@endauth
