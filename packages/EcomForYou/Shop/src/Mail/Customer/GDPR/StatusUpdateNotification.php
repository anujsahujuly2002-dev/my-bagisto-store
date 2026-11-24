<?php

namespace EcomForYou\Shop\Mail\Customer\GDPR;

use EcomForYou\Admin\Mail\Mailable;
use EcomForYou\GDPR\Contracts\GDPRDataRequest;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StatusUpdateNotification extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public GDPRDataRequest $gdprRequest) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address($this->gdprRequest->email),
            ],
            subject: trans('shop::app.emails.customers.gdpr.status-update.subject')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'shop::emails.customers.gdpr.status-update-notification',
        );
    }
}
