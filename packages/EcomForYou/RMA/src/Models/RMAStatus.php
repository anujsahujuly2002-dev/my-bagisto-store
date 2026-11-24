<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMAStatus as RMAStatusContract;
use Illuminate\Database\Eloquent\Model;

class RMAStatus extends Model implements RMAStatusContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'status',
        'color',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'default' => 'integer',
    ];
}
