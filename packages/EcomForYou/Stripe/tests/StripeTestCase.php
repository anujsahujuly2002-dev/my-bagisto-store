<?php

namespace EcomForYou\Stripe\Tests;

use EcomForYou\Payment\Tests\Concerns\ProvidePaymentHelpers;
use EcomForYou\Stripe\Tests\Concerns\FakesStripeApi;
use Tests\TestCase;

class StripeTestCase extends TestCase
{
    use FakesStripeApi, ProvidePaymentHelpers;
}
