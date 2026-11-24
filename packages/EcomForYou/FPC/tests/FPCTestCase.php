<?php

namespace EcomForYou\FPC\Tests;

use EcomForYou\Core\Tests\Concerns\CoreAssertions;
use EcomForYou\FPC\Tests\Concerns\FPCTestBench;
use EcomForYou\Product\Tests\Concerns\ProductTestBench;
use Tests\TestCase;

class FPCTestCase extends TestCase
{
    use CoreAssertions, FPCTestBench, ProductTestBench;
}
