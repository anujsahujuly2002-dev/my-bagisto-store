<?php

namespace EcomForYou\Stripe\Tests\Concerns;

use EcomForYou\Stripe\Tests\Fixtures\FakeStripeHttpClient;
use Stripe\ApiRequestor;

trait FakesStripeApi
{
    /**
     * Answer every call to the Stripe API from the given responses, handing it back to its real client when the test ends.
     */
    public function fakeStripeApi(array $responses = []): FakeStripeHttpClient
    {
        ApiRequestor::setHttpClient($client = new FakeStripeHttpClient($responses));

        $this->beforeApplicationDestroyed(fn () => ApiRequestor::setHttpClient(null));

        return $client;
    }
}
