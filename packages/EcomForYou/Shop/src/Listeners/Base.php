<?php

namespace EcomForYou\Shop\Listeners;

use EcomForYou\Sales\Contracts\OrderComment;
use Illuminate\Support\Facades\Mail;

class Base
{
    /**
     * Get the locale of the customer if somehow item name changes then the english locale will pe provided.
     *
     * @param object \EcomForYou\Sales\Contracts\Order|\EcomForYou\Sales\Contracts\Invoice|\EcomForYou\Sales\Contracts\Refund|\EcomForYou\Sales\Contracts\Shipment|\EcomForYou\Sales\Contracts\OrderComment
     * @return string
     */
    protected function getLocale($object)
    {
        if ($object instanceof OrderComment) {
            $object = $object->order;
        }

        $objectFirstItem = $object->items->first();

        return $objectFirstItem->additional['locale'] ?? 'en';
    }

    /**
     * Prepare mail.
     *
     * @return void
     */
    protected function prepareMail($entity, $notification)
    {
        $customerLocale = $this->getLocale($entity);

        $previousLocale = core()->getCurrentLocale()->code;

        app()->setLocale($customerLocale);

        try {
            Mail::queue($notification);
        } catch (\Exception $e) {
            \Log::error('Error in Sending Email'.$e->getMessage());
        }

        app()->setLocale($previousLocale);
    }
}
