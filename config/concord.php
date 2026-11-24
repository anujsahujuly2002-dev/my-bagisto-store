<?php

use EcomForYou\Admin\Providers\ModuleServiceProvider;
use EcomForYou\Core\CoreConvention;

return [

    /**
     * The path of the convention file.
     */
    'convention' => CoreConvention::class,

    /**
     * Whether Concord binds a route parameter named after a registered model, such as `{template}`, to that model.
     *
     * Off, so such a parameter stays a plain value unless the route type-hints a model for it.
     */
    'register_route_models' => false,

    /**
     * Example:
     *
     * VendorA\ModuleX\Providers\ModuleServiceProvider::class,
     * VendorB\ModuleY\Providers\ModuleServiceProvider::class,
     */
    'modules' => [
        ModuleServiceProvider::class,
        EcomForYou\Attribute\Providers\ModuleServiceProvider::class,
        EcomForYou\BookingProduct\Providers\ModuleServiceProvider::class,
        EcomForYou\CMS\Providers\ModuleServiceProvider::class,
        EcomForYou\CartRule\Providers\ModuleServiceProvider::class,
        EcomForYou\CatalogRule\Providers\ModuleServiceProvider::class,
        EcomForYou\Category\Providers\ModuleServiceProvider::class,
        EcomForYou\Checkout\Providers\ModuleServiceProvider::class,
        EcomForYou\Core\Providers\ModuleServiceProvider::class,
        EcomForYou\Customer\Providers\ModuleServiceProvider::class,
        EcomForYou\DataGrid\Providers\ModuleServiceProvider::class,
        EcomForYou\DataTransfer\Providers\ModuleServiceProvider::class,
        EcomForYou\EUWithdrawal\Providers\ModuleServiceProvider::class,
        EcomForYou\GDPR\Providers\ModuleServiceProvider::class,
        EcomForYou\Inventory\Providers\ModuleServiceProvider::class,
        EcomForYou\Marketing\Providers\ModuleServiceProvider::class,
        EcomForYou\Notification\Providers\ModuleServiceProvider::class,
        EcomForYou\Omnibus\Providers\ModuleServiceProvider::class,
        EcomForYou\PayGlocal\Providers\ModuleServiceProvider::class,
        EcomForYou\PayU\Providers\ModuleServiceProvider::class,
        EcomForYou\Payment\Providers\ModuleServiceProvider::class,
        EcomForYou\Paypal\Providers\ModuleServiceProvider::class,
        EcomForYou\Product\Providers\ModuleServiceProvider::class,
        EcomForYou\RMA\Providers\ModuleServiceProvider::class,
        EcomForYou\Razorpay\Providers\ModuleServiceProvider::class,
        EcomForYou\Rule\Providers\ModuleServiceProvider::class,
        EcomForYou\Sales\Providers\ModuleServiceProvider::class,
        EcomForYou\Shipping\Providers\ModuleServiceProvider::class,
        EcomForYou\Shop\Providers\ModuleServiceProvider::class,
        EcomForYou\Sitemap\Providers\ModuleServiceProvider::class,
        EcomForYou\SocialLogin\Providers\ModuleServiceProvider::class,
        EcomForYou\Stripe\Providers\ModuleServiceProvider::class,
        EcomForYou\Tax\Providers\ModuleServiceProvider::class,
        EcomForYou\Tenancy\Providers\ModuleServiceProvider::class,
        EcomForYou\Theme\Providers\ModuleServiceProvider::class,
        EcomForYou\User\Providers\ModuleServiceProvider::class,
    ],
];
