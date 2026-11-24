<?php

use App\Providers\AppServiceProvider;
use EcomForYou\Admin\Providers\AdminServiceProvider;
use EcomForYou\Attribute\Providers\AttributeServiceProvider;
use EcomForYou\BookingProduct\Providers\BookingProductServiceProvider;
use EcomForYou\CartRule\Providers\CartRuleServiceProvider;
use EcomForYou\CatalogRule\Providers\CatalogRuleServiceProvider;
use EcomForYou\Category\Providers\CategoryServiceProvider;
use EcomForYou\Checkout\Providers\CheckoutServiceProvider;
use EcomForYou\CMS\Providers\CMSServiceProvider;
use EcomForYou\Core\Providers\CoreServiceProvider;
use EcomForYou\Core\Providers\EnvValidatorServiceProvider;
use EcomForYou\Customer\Providers\CustomerServiceProvider;
use EcomForYou\DataGrid\Providers\DataGridServiceProvider;
use EcomForYou\DataTransfer\Providers\DataTransferServiceProvider;
use EcomForYou\DebugBar\Providers\DebugBarServiceProvider;
use EcomForYou\EUWithdrawal\Providers\EUWithdrawalServiceProvider;
use EcomForYou\FPC\Providers\FPCServiceProvider;
use EcomForYou\GDPR\Providers\GDPRServiceProvider;
use EcomForYou\ImageCache\Providers\ImageCacheServiceProvider;
use EcomForYou\Installer\Providers\InstallerServiceProvider;
use EcomForYou\Inventory\Providers\InventoryServiceProvider;
use EcomForYou\MagicAI\Providers\MagicAIServiceProvider;
use EcomForYou\Marketing\Providers\MarketingServiceProvider;
use EcomForYou\Notification\Providers\NotificationServiceProvider;
use EcomForYou\Omnibus\Providers\OmnibusServiceProvider;
use EcomForYou\PayGlocal\Providers\PayGlocalServiceProvider;
use EcomForYou\Payment\Providers\PaymentServiceProvider;
use EcomForYou\Paypal\Providers\PaypalServiceProvider;
use EcomForYou\PayU\Providers\PayUServiceProvider;
use EcomForYou\PhonePe\Providers\PhonePeServiceProvider;
use EcomForYou\Product\Providers\ProductServiceProvider;
use EcomForYou\Razorpay\Providers\RazorpayServiceProvider;
use EcomForYou\RMA\Providers\RMAServiceProvider;
use EcomForYou\Rule\Providers\RuleServiceProvider;
use EcomForYou\Sales\Providers\SalesServiceProvider;
use EcomForYou\Shipping\Providers\ShippingServiceProvider;
use EcomForYou\Shop\Providers\ShopServiceProvider;
use EcomForYou\Sitemap\Providers\SitemapServiceProvider;
use EcomForYou\SocialLogin\Providers\SocialLoginServiceProvider;
use EcomForYou\SocialShare\Providers\SocialShareServiceProvider;
use EcomForYou\Stripe\Providers\StripeServiceProvider;
use EcomForYou\Tax\Providers\TaxServiceProvider;
use EcomForYou\Theme\Providers\ThemeServiceProvider;
use EcomForYou\User\Providers\UserServiceProvider;

return [
    /**
     * Application service providers.
     */
    AppServiceProvider::class,

    /**
     * EcomForYou's service providers.
     */
    OmnibusServiceProvider::class,
    AdminServiceProvider::class,
    AttributeServiceProvider::class,
    BookingProductServiceProvider::class,
    CMSServiceProvider::class,
    CartRuleServiceProvider::class,
    CatalogRuleServiceProvider::class,
    CategoryServiceProvider::class,
    CheckoutServiceProvider::class,
    CoreServiceProvider::class,
    EnvValidatorServiceProvider::class,
    CustomerServiceProvider::class,
    DataGridServiceProvider::class,
    DataTransferServiceProvider::class,
    DebugBarServiceProvider::class,
    EUWithdrawalServiceProvider::class,
    FPCServiceProvider::class,
    GDPRServiceProvider::class,
    ImageCacheServiceProvider::class,
    InstallerServiceProvider::class,
    InventoryServiceProvider::class,
    MagicAIServiceProvider::class,
    MarketingServiceProvider::class,
    NotificationServiceProvider::class,
    PayGlocalServiceProvider::class,
    PayUServiceProvider::class,
    PaymentServiceProvider::class,
    PaypalServiceProvider::class,
    PhonePeServiceProvider::class,
    ProductServiceProvider::class,
    RMAServiceProvider::class,
    RazorpayServiceProvider::class,
    RuleServiceProvider::class,
    SalesServiceProvider::class,
    ShippingServiceProvider::class,
    ShopServiceProvider::class,
    SitemapServiceProvider::class,
    SocialLoginServiceProvider::class,
    SocialShareServiceProvider::class,
    StripeServiceProvider::class,
    TaxServiceProvider::class,
    ThemeServiceProvider::class,
    UserServiceProvider::class,
];
