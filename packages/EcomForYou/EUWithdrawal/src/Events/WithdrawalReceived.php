<?php

namespace EcomForYou\EUWithdrawal\Events;

use EcomForYou\EUWithdrawal\Models\Withdrawal;

class WithdrawalReceived
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Withdrawal $withdrawal) {}
}
