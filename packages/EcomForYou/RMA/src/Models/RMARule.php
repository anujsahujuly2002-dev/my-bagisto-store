<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMARule as RMARuleContract;
use Illuminate\Database\Eloquent\Model;

class RMARule extends Model implements RMARuleContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'return_period',
        'default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'default' => 'boolean',
        'return_period' => 'integer',
    ];
}
