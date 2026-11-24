<?php

namespace EcomForYou\Sales\Providers;

use EcomForYou\Core\Providers\CoreModuleServiceProvider;
use EcomForYou\Sales\Models\DownloadableLinkPurchased;
use EcomForYou\Sales\Models\Invoice;
use EcomForYou\Sales\Models\InvoiceItem;
use EcomForYou\Sales\Models\Order;
use EcomForYou\Sales\Models\OrderAddress;
use EcomForYou\Sales\Models\OrderComment;
use EcomForYou\Sales\Models\OrderItem;
use EcomForYou\Sales\Models\OrderPayment;
use EcomForYou\Sales\Models\OrderTransaction;
use EcomForYou\Sales\Models\Refund;
use EcomForYou\Sales\Models\RefundItem;
use EcomForYou\Sales\Models\Shipment;
use EcomForYou\Sales\Models\ShipmentItem;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        DownloadableLinkPurchased::class,
        Invoice::class,
        InvoiceItem::class,
        Order::class,
        OrderAddress::class,
        OrderComment::class,
        OrderItem::class,
        OrderPayment::class,
        OrderTransaction::class,
        Refund::class,
        RefundItem::class,
        Shipment::class,
        ShipmentItem::class,
    ];
}
