<?php

namespace EcomForYou\Admin\Listeners;

use EcomForYou\Admin\Mail\Customer\GDPR\NewRequestNotification;
use EcomForYou\Admin\Mail\Customer\GDPR\StatusUpdateNotification;
use EcomForYou\GDPR\Models\GDPRDataRequest;
use Illuminate\Support\Facades\Mail;

class GDPR extends Base
{
    /**
     * Send mail on creating GDPR request
     *
     * @param  GDPRDataRequest  $gdprRequest
     * @return void
     */
    public function afterGdprRequestCreated($gdprRequest)
    {
        try {
            Mail::queue(new NewRequestNotification($gdprRequest));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on creating GDPR request
     *
     * @param  GDPRDataRequest  $gdprRequest
     * @return void
     */
    public function afterGdprRequestUpdated($gdprRequest)
    {
        try {
            Mail::queue(new StatusUpdateNotification($gdprRequest));
        } catch (\Exception $e) {
            report($e);
        }
    }
}
