<?php

namespace EcomForYou\Sales\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Sales\Contracts\Invoice;
use EcomForYou\Sales\Contracts\OrderTransaction;

class OrderTransactionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return OrderTransaction::class;
    }

    /**
     * Record the payment taken for an invoice.
     */
    public function createForInvoice(Invoice $invoice): OrderTransaction
    {
        return $this->create([
            'transaction_id' => md5(uniqid()),
            'status' => $invoice->state,
            'type' => $invoice->order->payment->method,
            'payment_method' => $invoice->order->payment->method,
            'order_id' => $invoice->order->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->grand_total,
        ]);
    }
}
