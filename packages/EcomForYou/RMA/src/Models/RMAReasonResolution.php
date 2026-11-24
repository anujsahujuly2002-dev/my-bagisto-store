<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMAReasonResolution as RMAReasonResolutionContract;
use Illuminate\Database\Eloquent\Model;

class RMAReasonResolution extends Model implements RMAReasonResolutionContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_reason_resolutions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rma_reason_id',
        'resolution_type',
    ];
}
