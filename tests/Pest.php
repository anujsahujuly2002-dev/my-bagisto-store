<?php

use EcomForYou\Admin\Tests\AdminTestCase;
use EcomForYou\Category\Tests\CategoryTestCase;
use EcomForYou\Checkout\Tests\CheckoutTestCase;
use EcomForYou\Core\Tests\CoreTestCase;
use EcomForYou\Customer\Tests\CustomerTestCase;
use EcomForYou\DataGrid\Tests\DataGridTestCase;
use EcomForYou\EUWithdrawal\Tests\EUWithdrawalTestCase;
use EcomForYou\FPC\Tests\FPCTestCase;
use EcomForYou\Installer\Tests\InstallerTestCase;
use EcomForYou\Marketing\Tests\MarketingTestCase;
use EcomForYou\Omnibus\Tests\OmnibusTestCase;
use EcomForYou\PayGlocal\Tests\PayGlocalTestCase;
use EcomForYou\Payment\Tests\PaymentTestCase;
use EcomForYou\Paypal\Tests\PaypalTestCase;
use EcomForYou\PayU\Tests\PayUTestCase;
use EcomForYou\PhonePe\Tests\PhonePeTestCase;
use EcomForYou\Product\Tests\ProductTestCase;
use EcomForYou\Razorpay\Tests\RazorpayTestCase;
use EcomForYou\Rule\Tests\RuleTestCase;
use EcomForYou\Sales\Tests\SalesTestCase;
use EcomForYou\Shipping\Tests\ShippingTestCase;
use EcomForYou\Shop\Tests\ShopTestCase;
use EcomForYou\Stripe\Tests\StripeTestCase;
use EcomForYou\Tax\Tests\TaxTestCase;
use EcomForYou\Theme\Tests\ThemeTestCase;
use Illuminate\Support\Facades\Storage;
use Pest\Repositories\DatasetsRepository;

ini_set('memory_limit', '1024M');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(AdminTestCase::class)->in('../packages/EcomForYou/Admin/tests');
uses(CategoryTestCase::class)->in('../packages/EcomForYou/Category/tests');
uses(CheckoutTestCase::class)->in('../packages/EcomForYou/Checkout/tests');
uses(CoreTestCase::class)->in('../packages/EcomForYou/Core/tests');
uses(CustomerTestCase::class)->in('../packages/EcomForYou/Customer/tests');
uses(DataGridTestCase::class)->in('../packages/EcomForYou/DataGrid/tests');
uses(EUWithdrawalTestCase::class)->in('../packages/EcomForYou/EUWithdrawal/tests');
uses(FPCTestCase::class)->in('../packages/EcomForYou/FPC/tests');
uses(InstallerTestCase::class)->in('../packages/EcomForYou/Installer/tests');
uses(OmnibusTestCase::class)->in('../packages/EcomForYou/Omnibus/tests');
uses(PayGlocalTestCase::class)->in('../packages/EcomForYou/PayGlocal/tests');
uses(PaymentTestCase::class)->in('../packages/EcomForYou/Payment/tests');
uses(PaypalTestCase::class)->in('../packages/EcomForYou/Paypal/tests');
uses(PayUTestCase::class)->in('../packages/EcomForYou/PayU/tests');
uses(PhonePeTestCase::class)->in('../packages/EcomForYou/PhonePe/tests');
uses(ProductTestCase::class)->in('../packages/EcomForYou/Product/tests');
uses(RazorpayTestCase::class)->in('../packages/EcomForYou/Razorpay/tests');
uses(RuleTestCase::class)->in('../packages/EcomForYou/Rule/tests');
uses(MarketingTestCase::class)->in('../packages/EcomForYou/Marketing/tests');
uses(SalesTestCase::class)->in('../packages/EcomForYou/Sales/tests');
uses(ShippingTestCase::class)->in('../packages/EcomForYou/Shipping/tests');
uses(ShopTestCase::class)->in('../packages/EcomForYou/Shop/tests');
uses(StripeTestCase::class)->in('../packages/EcomForYou/Stripe/tests');
uses(TaxTestCase::class)->in('../packages/EcomForYou/Tax/tests');
uses(ThemeTestCase::class)->in('../packages/EcomForYou/Theme/tests');

/*
|--------------------------------------------------------------------------
| Storage Isolation
|--------------------------------------------------------------------------
|
| Editing or removing a theme section deletes its uploads from the disk, and a rolled back
| transaction does not put a deleted file back. The screens that do it work on a temporary
| disk, so a run against a developer's own database leaves that store's media alone.
|
*/

uses()->beforeEach(fn () => Storage::fake())->in('../packages/EcomForYou/Admin/tests/Feature/Appearance');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Custom expectations shared by every package suite. Prices are compared within half of the
| current currency's smallest unit, so a test never fails on a floating point tail the customer
| would never see — including one that falls either side of a rounding boundary, which comparing
| the formatted strings turns into a whole unit of difference.
|
*/

expect()->extend('toBePrice', function (float $expected, ?int $decimal = null) {
    $decimal ??= core()->getCurrentCurrency()->decimal;

    $actual = (float) $this->value;

    if (abs($actual - $expected) <= 0.5 / 10 ** $decimal) {
        $actual = $expected;
    }

    expect(number_format($actual, $decimal))->toBe(number_format($expected, $decimal));

    return $this;
});

/*
|--------------------------------------------------------------------------
| Datasets
|--------------------------------------------------------------------------
|
| Datasets shared by every package suite live in tests/Datasets and are loaded by Pest on
| boot. Pest scopes a dataset to the directory it is declared in, which would hide one
| declared here from the package suites, so they are registered against the repository
| root instead. Package-specific datasets belong next to the tests that use them.
|
*/

/**
 * Register a dataset every test in the repository can use, whichever package it lives in.
 */
function sharedDataset(string $name, Closure|iterable $dataset): void
{
    DatasetsRepository::set($name, $dataset, dirname(__DIR__));
}
