<?php

namespace EcomForYou\Sales\Tests;

use EcomForYou\Core\Tests\Concerns\CoreAssertions;
use EcomForYou\Product\Tests\Concerns\ProductTestBench;
use EcomForYou\Sales\Tests\Concerns\OrderTestBench;
use Tests\TestCase;

class SalesTestCase extends TestCase
{
    use CoreAssertions, OrderTestBench, ProductTestBench;
}
