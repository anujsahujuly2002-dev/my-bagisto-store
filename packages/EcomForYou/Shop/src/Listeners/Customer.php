<?php

namespace EcomForYou\Shop\Listeners;

use EcomForYou\Customer\Models\CustomerNote;
use EcomForYou\Shop\Mail\Customer\EmailVerificationNotification;
use EcomForYou\Shop\Mail\Customer\NoteNotification;
use EcomForYou\Shop\Mail\Customer\RegistrationNotification;
use EcomForYou\Shop\Mail\Customer\SubscriptionNotification;
use EcomForYou\Shop\Mail\Customer\UpdatePasswordNotification;
use Illuminate\Support\Facades\Mail;

class Customer extends Base
{
    /**
     * After customer is created
     *
     * @param  \EcomForYou\Customer\Contracts\Customer  $customer
     * @return void
     */
    public function afterCreated($customer)
    {
        if (core()->getConfigData('customer.settings.email.verification')) {
            try {
                if (! core()->getConfigData('customer.settings.email.verification')) {
                    return;
                }

                Mail::queue(new EmailVerificationNotification($customer));
            } catch (\Exception $e) {
                \Log::info('EmailVerificationNotification Error');

                report($e);
            }

            return;
        }

        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.registration')) {
                return;
            }

            Mail::queue(new RegistrationNotification($customer));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on updating password.
     *
     * @param  \EcomForYou\Customer\Models\Customer  $customer
     * @return void
     */
    public function afterPasswordUpdated($customer)
    {
        try {
            Mail::queue(new UpdatePasswordNotification($customer));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on subscribe
     *
     * @param  \EcomForYou\Customer\Models\Customer  $customer
     * @return void
     */
    public function afterSubscribed($customer)
    {
        try {
            Mail::queue(new SubscriptionNotification($customer));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on creating Note
     *
     * @param  CustomerNote  $note
     * @return void
     */
    public function afterNoteCreated($note)
    {
        if (! $note->customer_notified) {
            return;
        }

        try {
            Mail::queue(new NoteNotification($note));
        } catch (\Exception $e) {
            session()->flash('warning', $e->getMessage());
        }
    }
}
