<?php

namespace EcomForYou\Admin\Listeners;

use EcomForYou\Admin\Mail\Order\InvoicedNotification;

class Invoice extends Base
{
    /**
     * After the invoice is created.
     *
     * @param  \EcomForYou\Sales\Contracts\Invoice  $invoice
     * @return void
     */
    public function afterCreated($invoice)
    {
        $this->sendMail($invoice);
    }

    /**
     * Send the new invoice notification to the admin.
     *
     * @param  \EcomForYou\Sales\Contracts\Invoice  $invoice
     * @return void
     */
    public function sendMail($invoice)
    {
        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.new_invoice_mail_to_admin')) {
                return;
            }

            $this->prepareMail($invoice, new InvoicedNotification($invoice));
        } catch (\Exception $e) {
            report($e);
        }
    }
}
