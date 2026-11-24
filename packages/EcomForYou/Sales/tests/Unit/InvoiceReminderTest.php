<?php

use EcomForYou\Sales\Models\Invoice;
use EcomForYou\Shop\Mail\Customer\InvoiceOverdueReminder;
use Illuminate\Support\Facades\Mail;

// ============================================================================
// Overdue Reminders
// ============================================================================

it('should send the overdue reminder to the customer who placed the order', function () {
    Mail::fake();

    $order = $this->createOrder();

    $invoice = $this->invoiceOrder($order, state: Invoice::STATUS_OVERDUE);

    $this->artisan('invoice:cron')->assertSuccessful();

    Mail::assertQueued(
        InvoiceOverdueReminder::class,
        fn (InvoiceOverdueReminder $mail) => $mail->invoice->is($invoice)
            && $mail->hasTo($order->customer_email)
    );

    expect($invoice->refresh()->reminders)->toBe(1);
});

it('should not send the overdue reminder to the store sender address', function () {
    Mail::fake();

    $invoice = $this->invoiceOrder($this->createOrder(), state: Invoice::STATUS_OVERDUE);

    $this->artisan('invoice:cron')->assertSuccessful();

    Mail::assertNotQueued(
        InvoiceOverdueReminder::class,
        fn (InvoiceOverdueReminder $mail) => $mail->invoice->is($invoice)
            && $mail->hasTo(core()->getSenderEmailDetails()['email'])
    );
});
