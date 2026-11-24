<?php

namespace EcomForYou\Admin\Listeners;

use EcomForYou\Admin\SystemHealth\Checks\ExchangeRatesCheck;
use EcomForYou\Admin\SystemHealth\Heartbeat;

class ExchangeRate
{
    /**
     * Create a new listener instance.
     */
    public function __construct(protected Heartbeat $heartbeat) {}

    /**
     * Record that the exchange rates were updated, for the system health check.
     */
    public function afterRatesUpdated(): void
    {
        $this->heartbeat->beat(ExchangeRatesCheck::HEARTBEAT);
    }
}
