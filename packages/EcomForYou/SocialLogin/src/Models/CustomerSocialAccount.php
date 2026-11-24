<?php

namespace EcomForYou\SocialLogin\Models;

use EcomForYou\Customer\Models\CustomerProxy;
use EcomForYou\SocialLogin\Contracts\CustomerSocialAccount as CustomerSocialAccountContract;
use Illuminate\Database\Eloquent\Model;

class CustomerSocialAccount extends Model implements CustomerSocialAccountContract
{
    protected $fillable = [
        'customer_id',
        'provider_name',
        'provider_id',
    ];

    /**
     * Get the customer that belongs to the social aoount.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
