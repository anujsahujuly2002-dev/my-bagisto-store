@if (core()->getConfigData('sales.checkout.shopping_cart.cart_page'))
    <div class="bagisto-cart">
        @include('shop::checkout.cart.mini-cart')
    </div>
@endif
