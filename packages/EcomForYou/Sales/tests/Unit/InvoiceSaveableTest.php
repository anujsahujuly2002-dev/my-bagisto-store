<?php

use EcomForYou\Sales\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Schema;

// ============================================================================
// An Invoice Stays Saveable After Creation
// ============================================================================

it('should leave no attribute on the invoice that its table has no column for', function () {
    $invoice = $this->invoiceOrder($this->createOrder());

    expect(array_diff(array_keys($invoice->getAttributes()), Schema::getColumnListing($invoice->getTable())))
        ->toBe([]);
});

it('should still save an invoice the repository has just created', function () {
    $invoice = $this->invoiceOrder($this->createOrder());

    app(InvoiceRepository::class)->updateState($invoice, 'paid');

    expect($invoice->refresh()->state)->toBe('paid');
});
