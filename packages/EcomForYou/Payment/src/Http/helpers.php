<?php

use EcomForYou\Payment\Facades\Payment;

if (! function_exists('payment')) {
    /**
     * Payment helper.
     *
     * @return EcomForYou\Payment\Payment
     */
    function payment()
    {
        return Payment::getFacadeRoot();
    }
}
