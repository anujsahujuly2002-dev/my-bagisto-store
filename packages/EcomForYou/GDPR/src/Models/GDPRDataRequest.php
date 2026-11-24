<?php

namespace EcomForYou\GDPR\Models;

use EcomForYou\Customer\Models\CustomerProxy;
use EcomForYou\GDPR\Contracts\GDPRDataRequest as GDPRDataRequestContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GDPRDataRequest extends Model implements GDPRDataRequestContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'gdpr_data_request';

    /**
     * Summary of fillable fields.
     */
    protected $fillable = [
        'customer_id',
        'email',
        'status',
        'type',
        'message',
        'revoked_at',
    ];

    /**
     * Get the customer record associated with the GDPR.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
