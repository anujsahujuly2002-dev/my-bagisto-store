<?php

namespace EcomForYou\Shop\Tests\Concerns;

use EcomForYou\Product\Tests\Concerns\ProductTestBench;
use EcomForYou\Sales\Tests\Concerns\OrderTestBench;

trait ShopTestBench
{
    use AssertionHelpers;
    use AuthHelpers;
    use CartHelpers;
    use CheckoutHelpers;
    use OrderTestBench;
    use PricingHelpers;
    use ProductTestBench;
}
